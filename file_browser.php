<?php
// file_browser.php
// ────────────────────────────────────────────────────────────────────────────
//  • GitHub-style “Raw” support
//  • View vs. Edit tabs
//  • Autosave + explicit “Save” button
//  • Detailed logging on every save attempt
//  • Restrict edits to text-based file types only
//  • “Raw Links” panel next to file browser, with Copy All per category
// ────────────────────────────────────────────────────────────────────────────

$ENABLE_EDIT_MODE = false; // Toggle this to enable/disable editing

ini_set('display_errors', 1);
ini_set('log_errors',     1);
error_reporting(E_ALL);

// Base directory (only allow browsing inside here)
$BASE_DIR = realpath(__DIR__);

/**
 * normalizePath(): Given a base and relative target, returns the realpath
 * if it’s inside $base, or null otherwise.
 */
function normalizePath(string $base, string $target): ?string {
    $full = realpath($base . DIRECTORY_SEPARATOR . $target);
    if ($full === false || strpos($full, $base) !== 0) {
        return null;
    }
    return $full;
}

/**
 * isEditableFile(): true for text/* or known text extensions.
 */
function isEditableFile(string $fullPath): bool {
    $mime = mime_content_type($fullPath) ?: '';
    $ext  = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $textExts = ['php','css','js','json','xml','html','htm','md','txt'];
    return (strpos($mime, 'text/') === 0) || in_array($ext, $textExts, true);
}

// ────────────────────────────────────────────────────────────────────────────
// RAW FILE HANDLER
// ────────────────────────────────────────────────────────────────────────────
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/raw/') !== false) {
    $rawPath     = urldecode(explode('/raw/', $_SERVER['REQUEST_URI'], 2)[1] ?? '');
    $fullRawPath = normalizePath($BASE_DIR, $rawPath);
    if ($fullRawPath && is_file($fullRawPath) && isEditableFile($fullRawPath)) {
        header('Content-Type: text/plain; charset=utf-8');
        readfile($fullRawPath);
        exit;
    }
    http_response_code(404);
    echo "404 Not Found: Raw file not accessible.";
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// HANDLE POST (save edits)
// ────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && $ENABLE_EDIT_MODE
    && isset($_POST['path'], $_POST['contents'])
) {
    $postedPath = $_POST['path'];
    $postedFull = normalizePath($BASE_DIR, $postedPath);

    file_put_contents(
        "$BASE_DIR/error_log_debug.txt",
        "[" . date('Y-m-d H:i:s') . "] POST save: $postedPath\n",
        FILE_APPEND
    );

    if (!$postedFull || !file_exists($postedFull) || !isEditableFile($postedFull)) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>"Invalid or non-editable: $postedPath"]);
        exit;
    }

    $bytes = @file_put_contents($postedFull, $_POST['contents']);
    if ($bytes === false) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>"Write error"]);
        exit;
    }

    echo json_encode(['success'=>true,'bytes'=>$bytes]);
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// Determine which file/dir the user requested
// ────────────────────────────────────────────────────────────────────────────
$requested = $_GET['path'] ?? '';
$fullPath  = normalizePath($BASE_DIR, $requested);

if (!$fullPath || !file_exists($fullPath)) {
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>404</title></head>"
       ."<body><h2>404 Not Found</h2><p>“".htmlspecialchars($requested)."” not found.</p></body></html>";
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// DIRECTORY VIEW: show file-browser + Raw Links panel side by side
// ────────────────────────────────────────────────────────────────────────────
if (is_dir($fullPath)) {
    $items = scandir($fullPath);

    // Build groups by extension
    $groups = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($BASE_DIR, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $fileinfo) {
        if (!$fileinfo->isFile()) continue;
        $rel = ltrim(str_replace($BASE_DIR, '', $fileinfo->getRealPath()), DIRECTORY_SEPARATOR);
        $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION)) ?: 'none';
        $groups[$ext][] = $rel;
    }

    // HTML + CSS + JS
    echo "<!DOCTYPE html>\n<html><head><meta charset='utf-8'>\n"
       ."<title>Browsing “".htmlspecialchars($requested ?: "/")."”</title>\n"
       ."<style>
          body { font-family:sans-serif; padding:1rem; }
          .container { display:flex; justify-content:center; gap:2rem; }
          .file-browser, .raw-links { width:400px; }
          .file-browser ul, .raw-links ul { list-style:none; padding-left:0; }
          .file-browser li { margin:.3rem 0; }
          .raw-links h3 { margin-top:1rem; }
          .copy-btn { margin-bottom:.5rem; padding:.3rem .6rem; cursor:pointer; }
          a { color:#007bff; text-decoration:none; }
          a:hover { text-decoration:underline; }
        </style>\n"
       ."<script>
          function copyCategory(ext) {
            var cont = document.getElementById('raw-'+ext);
            var links = cont.querySelectorAll('a');
            var txt = Array.from(links).map(a=>a.href).join('\\n');
            navigator.clipboard.writeText(txt)
              .then(_=>alert('Copied '+links.length+' links'))
              .catch(_=>alert('Copy failed'));
          }
        </script>\n"
       ."</head><body>\n";

    // Breadcrumb / heading
    echo "<h2>Browsing “".htmlspecialchars($requested ?: "/")."”</h2>\n";
    if (realpath($fullPath) !== $BASE_DIR) {
        $parent = dirname($requested);
        echo "<p><a href=\"?path=".urlencode($parent)."\">&larr; Up one level</a></p>\n";
    }

    // Two panels side by side
    echo "<div class='container'>\n";

    // Left: normal file browser
    echo "<div class='file-browser'>\n<ul>\n";
    foreach ($items as $name) {
        if ($name==='.'||$name==='..') continue;
        $rel = ($requested==='') ? $name : ($requested.'/'.$name);
        $href = "?path=".urlencode($rel)."&mode=view";
        $raw  = htmlspecialchars($_SERVER['PHP_SELF'])."/raw/".rawurlencode($rel);
        if (is_dir($fullPath.DIRECTORY_SEPARATOR.$name)) {
            echo "<li>📁 <a href=\"$href\">".htmlspecialchars($name)."</a></li>\n";
        } else {
            echo "<li>📄 <a href=\"$href\">".htmlspecialchars($name)."</a> | "
               ."<a href=\"$raw\" target=\"_blank\">[raw]</a></li>\n";
        }
    }
    echo "</ul>\n</div>\n"; // .file-browser

    // Right: raw-links grouped
    echo "<div class='raw-links'>\n<h3>Raw Links</h3>\n";
    foreach ($groups as $ext => $files) {
        echo "<div id='raw-".htmlspecialchars($ext)."'>\n"
           ."<h3>".strtoupper(htmlspecialchars($ext))."</h3>\n"
           ."<button class='copy-btn' onclick=\"copyCategory('".htmlspecialchars($ext)."')\">Copy All</button>\n"
           ."<ul>\n";
        foreach ($files as $f) {
            $raw = htmlspecialchars($_SERVER['PHP_SELF'])."/raw/".rawurlencode($f);
            echo "<li><a href=\"$raw\" target=\"_blank\">./".htmlspecialchars($f)."</a></li>\n";
        }
        echo "</ul>\n</div>\n";
    }
    echo "</div>\n"; // .raw-links

    echo "</div>\n"; // .container
    echo "</body></html>";
    exit;
}

// ────────────────────────────────────────────────────────────────────────────
// FILE VIEW / EDIT (unchanged from original)
// ────────────────────────────────────────────────────────────────────────────
$mime = mime_content_type($fullPath) ?: 'application/octet-stream';
$size = filesize($fullPath);
$mode = $_GET['mode'] ?? 'view';

echo "<!DOCTYPE html>\n<html><head><meta charset='utf-8'>\n"
   ."<title>“".htmlspecialchars($requested)."”</title>\n"
   ."<style>
       body { font-family:sans-serif; padding:1rem; }
       pre { background:#272822; color:#f8f8f2; padding:1rem; overflow-x:auto; }
       textarea { width:100%; height:70vh; font-family:monospace; font-size:.9rem; }
       .tabs a { margin-right:1rem; text-decoration:none; padding:.3rem .6rem; border:1px solid #ccc; }
       .tabs a.active { background:#f8f8f8; border-top:2px solid #007bff; }
       .meta { margin-bottom:.5rem; font-size:.9rem; color:#555; }
       .status { position:fixed; bottom:1rem; right:1rem; padding:.5rem 1rem; border-radius:3px; background:rgba(0,0,0,.7); color:#fff; display:none; }
       .save-btn { margin-bottom:.5rem; padding:.4rem 1rem; background:#007bff; color:#fff; border:none; border-radius:3px; cursor:pointer; }
       .save-btn:hover { background:#0056b3; }
     </style>\n"
   ."<script>
       let saveTimeout;
       function showStatus(msg, ok=true){
         const el = document.getElementById('autosaveStatus');
         el.textContent=msg;
         el.style.background= ok ? 'rgba(0,128,0,.8)' : 'rgba(128,0,0,.8)';
         el.style.display='block';
         clearTimeout(window.hide);
         window.hide=setTimeout(()=>el.style.display='none',1500);
       }
       function autoSave(path){
         clearTimeout(saveTimeout);
         saveTimeout=setTimeout(()=>{
           const cnt=document.getElementById('editor').value;
           const xhr=new XMLHttpRequest();
           xhr.open('POST','".htmlspecialchars($_SERVER['PHP_SELF'])."',true);
           xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
           xhr.onreadystatechange=function(){
             if(xhr.readyState===4){
               if(xhr.status===200){
                 try{
                   const r=JSON.parse(xhr.responseText);
                   showStatus(r.success?'Saved.':'Error:'+r.error,false);
                 }catch(e){ showStatus('Save error',false); }
               } else { showStatus('Save failed',false); }
             }
           };
           xhr.send('path='+encodeURIComponent(path)+'&contents='+encodeURIComponent(cnt));
         },800);
       }
       function manualSave(path){
         clearTimeout(saveTimeout);
         autoSave(path);
       }
       window.addEventListener('DOMContentLoaded',()=>{
         const ed=document.getElementById('editor');
         if(ed) ed.addEventListener('input',()=>autoSave(ed.dataset.path));
       });
     </script>\n"
   ."</head><body>\n";

// Breadcrumb
$parentDir = dirname($requested);
$rawUrl    = htmlspecialchars($_SERVER['PHP_SELF'])."/raw/".rawurlencode($requested);
echo "<p class='meta'><a href='?path=".urlencode($parentDir)."'>← Back</a>"
   ." | Size: {$size} bytes"
   ." | MIME: ".htmlspecialchars($mime)
   ." | <a href='{$rawUrl}' target='_blank'>Raw view</a></p>\n";

// Tabs
$viewLink = "?path=".urlencode($requested)."&mode=view";
$editLink = "?path=".urlencode($requested)."&mode=edit";
echo "<div class='tabs'>"
   ."<a href='{$viewLink}' class='".($mode==='view'?'active':'')."'>View</a>";
if ($ENABLE_EDIT_MODE && isEditableFile($fullPath)) {
    echo "<a href='{$editLink}' class='".($mode==='edit'?'active':'')."'>Edit</a>";
}
echo "</div>\n";

if ($mode==='view') {
    if (isEditableFile($fullPath)) {
        echo "<pre>".htmlspecialchars(file_get_contents($fullPath))."</pre>\n";
    } else {
        echo "<p>Cannot preview this file (binary or unsupported).</p>\n";
    }
    echo "</body></html>";
    exit;
}

// EDIT mode
if ($mode==='edit') {
    if (!$ENABLE_EDIT_MODE || !isEditableFile($fullPath)) {
        echo "<p>Cannot edit (binary or unsupported).</p></body></html>";
        exit;
    }
    $code = htmlspecialchars(file_get_contents($fullPath));
    echo "<button class='save-btn' onclick=\"manualSave('".addslashes($requested)."')\">Save</button>\n"
       ."<textarea id='editor' data-path='".htmlspecialchars($requested)."'>$code</textarea>\n"
       ."<div id='autosaveStatus' class='status'></div>\n"
       ."</body></html>";
    exit;
}

// Fallback
header("Location: ?path=".urlencode($requested)."&mode=view");
exit;
