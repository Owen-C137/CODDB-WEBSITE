<?php
// file_browser.php with GitHub-style raw support and easy edit mode toggle

$ENABLE_EDIT_MODE = false; // ✅ Toggle this to true to enable file editing

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
file_put_contents("error_log_debug.txt", print_r($_SERVER, true), FILE_APPEND);

$BASE_DIR = realpath(__DIR__);

function normalizePath(string $base, string $target): ?string {
    $full = realpath($base . DIRECTORY_SEPARATOR . $target);
    if ($full === false) return null;
    if (strpos($full, $base) !== 0) return null;
    return $full;
}

function isEditableFile(string $fullPath): bool {
    $mime = mime_content_type($fullPath) ?: '';
    $ext  = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $textExts = ['php','css','js','json','xml','html','htm','md','txt'];
    if (strpos($mime, 'text/') === 0) return true;
    if (in_array($ext, $textExts, true)) return true;
    return false;
}

// RAW FILE HANDLER
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/raw/') !== false) {
    $rawPath = explode('/raw/', $_SERVER['REQUEST_URI'], 2)[1] ?? '';
    $rawPath = urldecode($rawPath);
    $fullRawPath = normalizePath($BASE_DIR, $rawPath);

    if ($fullRawPath && file_exists($fullRawPath) && is_file($fullRawPath) && isEditableFile($fullRawPath)) {
        header('Content-Type: text/plain; charset=utf-8');
        readfile($fullRawPath);
        exit;
    } else {
        http_response_code(404);
        echo "404 Not Found: Raw file not accessible.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ENABLE_EDIT_MODE && isset($_POST['path']) && isset($_POST['contents'])) {
    $postedPath = $_POST['path'];
    $postedFull = normalizePath($BASE_DIR, $postedPath);

    if ($postedFull === null || !file_exists($postedFull) || !isEditableFile($postedFull)) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'Invalid file or not editable.']);
        exit;
    }

    $newContent = $_POST['contents'];
    $bytes = @file_put_contents($postedFull, $newContent);
    if ($bytes === false) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Failed to save file.']);
        exit;
    }

    echo json_encode(['success'=>true,'bytes'=>$bytes]);
    exit;
}

$requested = $_GET['path'] ?? '';
$fullPath  = normalizePath($BASE_DIR, $requested);

if ($fullPath === null || !file_exists($fullPath)) {
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>404 Not Found</title></head>"
       . "<body><h2>404 Not Found</h2><p>Could not find “" . htmlspecialchars($requested) . "”.</p></body></html>";
    exit;
}

if (is_dir($fullPath)) {
    $items = scandir($fullPath);
    echo "<!DOCTYPE html>\n<html><head><meta charset='utf-8'>\n"
       . "<title>Browsing “" . htmlspecialchars($requested ?: "/") . "”</title>\n"
       . "<style>
            body { font-family: sans-serif; padding: 1rem; }
            a { text-decoration: none; color: #007bff; }
            a:hover { text-decoration: underline; }
            ul { list-style: none; padding-left: 1rem; }
            li { margin: 0.2rem 0; }
            .dir { font-weight: bold; }
         </style>\n"
       . "</head><body>\n";
    echo "<h2>Browsing “" . htmlspecialchars($requested ?: "/") . "”</h2>\n";
    if (realpath($fullPath) !== $BASE_DIR) {
        $parent = dirname($requested);
        echo "<p><a href=\"?path=" . urlencode($parent) . "\">&larr; Up one level</a></p>\n";
    }
    echo "<ul>\n";
    foreach ($items as $name) {
        if ($name === '.' || $name === '..') continue;
        $rel = ($requested === '') ? $name : ($requested . '/' . $name);
        $href = "?path=" . urlencode($rel) . "&mode=view";
        $rawUrl = htmlspecialchars($_SERVER['SCRIPT_NAME']) . "/raw/" . rawurlencode($rel);
        if (is_dir($fullPath . DIRECTORY_SEPARATOR . $name)) {
            echo "<li><span class='dir'>📁 <a href=\"$href\">" 
               . htmlspecialchars($name)
               . "</a></span></li>\n";
        } else {
            echo "<li>📄 <a href=\"$href\">" 
               . htmlspecialchars($name) 
               . "</a> | <a href=\"$rawUrl\" target=\"_blank\">[raw]</a></li>\n";
        }
    }
    echo "</ul>\n";
    echo "</body></html>\n";
    exit;
}

$mime      = mime_content_type($fullPath) ?: 'application/octet-stream';
$size      = filesize($fullPath);
$mode      = $_GET['mode'] ?? 'view';

echo "<!DOCTYPE html>\n<html><head><meta charset='utf-8'>\n"
   . "<title>“" . htmlspecialchars($requested) . "”</title>\n"
   . "<style>
        body { font-family: sans-serif; padding: 1rem; }
        pre { background: #272822; color: #f8f8f2; padding: 1rem; overflow-x: auto; }
        textarea { width: 100%; height: 80vh; font-family: monospace; font-size: 0.9rem; }
        .tabs { margin-bottom: 1rem; }
        .tabs a { margin-right: 1rem; text-decoration: none; padding: 0.3rem 0.6rem; border: 1px solid #ccc; border-bottom: none; }
        .tabs a.active { background: #f8f8f8; border-top: 2px solid #007bff; }
        .meta { margin-bottom: 0.5rem; font-size: 0.9rem; color: #555; }
        .status { position: fixed; bottom: 1rem; right: 1rem; padding: 0.5rem 1rem; border-radius: 3px; background: rgba(0,0,0,0.7); color: #fff; display: none; }
      </style>\n"
   . "<script>
        let saveTimeout;
        function showStatus(msg, success = true) {
          const el = document.getElementById('autosaveStatus');
          el.textContent = msg;
          el.style.background = success ? 'rgba(0,128,0,0.8)' : 'rgba(128,0,0,0.8)';
          el.style.display = 'block';
          clearTimeout(window.statusHide);
          window.statusHide = setTimeout(() => el.style.display = 'none', 1500);
        }

        function autoSave(path) {
          clearTimeout(saveTimeout);
          saveTimeout = setTimeout(() => {
            const contents = document.getElementById('editor').value;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
              if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                  try {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                      showStatus('Saved.');
                    } else {
                      showStatus('Error: ' + (resp.error||'Unknown'), false);
                    }
                  } catch(e) {
                    showStatus('Save error', false);
                  }
                } else {
                  showStatus('Save failed', false);
                }
              }
            };
            const payload = 'path=' + encodeURIComponent(path)
                          + '&contents=' + encodeURIComponent(contents);
            xhr.send(payload);
          }, 800);
        }

        window.addEventListener('DOMContentLoaded', () => {
          const editor = document.getElementById('editor');
          if (editor) {
            const path = editor.getAttribute('data-path');
            editor.addEventListener('input', () => autoSave(path));
          }
        });
      </script>\n"
   . "</head><body>\n";

$parentDir = dirname($requested);
$rawUrl = htmlspecialchars($_SERVER['SCRIPT_NAME']) . "/raw/" . rawurlencode($requested);
echo "<p class='meta'>"
   . "<a href=\"?path=" . urlencode($parentDir) . "\">&larr; Back to Directory</a>"
   . " &nbsp;|&nbsp; Size: {$size} bytes"
   . " &nbsp;|&nbsp; MIME: " . htmlspecialchars($mime)
   . " &nbsp;|&nbsp; <a href=\"$rawUrl\" target=\"_blank\">Raw view</a>"
   . "</p>\n";

$viewLink = "?path=" . urlencode($requested) . "&mode=view";
$editLink = "?path=" . urlencode($requested) . "&mode=edit";
echo "<div class='tabs'>\n";
echo "  <a href=\"$viewLink\" class='" . ($mode === 'view' ? 'active' : '') . "'>View</a>\n";
if ($ENABLE_EDIT_MODE && isEditableFile($fullPath)) {
    echo "  <a href=\"$editLink\" class='" . ($mode === 'edit' ? 'active' : '') . "'>Edit</a>\n";
}
echo "</div>\n";

if ($mode === 'view') {
    if (isEditableFile($fullPath)) {
        $code = file_get_contents($fullPath);
        echo "<pre>" . htmlspecialchars($code) . "</pre>\n";
    } else {
        echo "<p>Cannot preview this file. It may be binary.</p>";
    }
    echo "</body></html>\n";
    exit;
}

if ($mode === 'edit') {
    if (!$ENABLE_EDIT_MODE || !isEditableFile($fullPath)) {
        echo "<p>Cannot edit this file (binary or unsupported type).</p>";
        echo "</body></html>\n";
        exit;
    }
    $code = file_get_contents($fullPath);
    echo "<textarea id='editor' data-path=\"" . htmlspecialchars($requested) . "\">"
       . htmlspecialchars($code)
       . "</textarea>\n";
    echo "<div id='autosaveStatus' class='status'></div>\n";
    echo "</body></html>\n";
    exit;
}

header("Location: ?path=" . urlencode($requested) . "&mode=view");
exit;