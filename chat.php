<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/_global/login_check.php'; ?>
<?php require 'inc/backend/config.php'; ?>

<?php
// Codebase - Page specific configuration
$cb->l_m_content = 'narrow';

// Determine current user role
$stmt = $pdo->prepare("SELECT role_id FROM demon_users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $_SESSION['user_id']]);
$currentRole = (int)$stmt->fetchColumn();

// Generate chat CSRF token
if (empty($_SESSION['chat_csrf_token'])) {
    $_SESSION['chat_csrf_token'] = bin2hex(random_bytes(32));
}
$chatCsrf = $_SESSION['chat_csrf_token'];
?>
<?php require 'inc/_global/views/head_start.php'; ?>
<style>
  /* ──── Pinned Message Bar ──── */
  #chat-pinned {
    display: none;
    padding: .75rem 1rem;
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-size: .95rem;
    color: #856404;
  }

  /* ──── Messages container: hide scrollbar until hover ──── */
  .js-chat-messages {
    overflow-y: auto;
    max-height: 400px;
    scrollbar-width: none; /* Firefox */
  }
  .js-chat-messages::-webkit-scrollbar {
    width: 0;
    background: transparent;
  }
  .js-chat-messages:hover {
    scrollbar-width: thin;
  }
  .js-chat-messages:hover::-webkit-scrollbar {
    width: 6px;
  }
  .js-chat-messages:hover::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, .2);
    border-radius: 3px;
  }

  /* ──── Emoji Picker (initially hidden) ──── */
  #emoji-picker {
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    position: absolute;
    z-index: 1000;
    width: 220px;
    max-height: 180px;
    overflow-y: auto;
    padding: 8px;
    display: none;
  }
  .emoji-btn {
    font-size: 1.2rem;
    border: none;
    background: transparent;
    cursor: pointer;
    margin: 2px;
  }
  .emoji-btn:hover {
    background: #f0f0f0;
    border-radius: 4px;
  }

  /* ──── Override nested row/col in Who’s Online include ──── */
  #chat-whos-online .row {
    margin-left: 0;
    margin-right: 0;
  }
  #chat-whos-online .col-md-3 {
    flex: 0 0 100%;
    max-width: 100%;
  }
  #chat-whos-online .list-group {
    margin-top: .5rem;
  }
</style>
<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<div class="content">
  <h2 class="content-heading">Chat Room</h2>

  <?php if ($currentRole === 1): // Admin-only chat settings ?>
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <strong>Chat Settings (Admin)</strong>
      </div>
      <ul class="list-group list-group-flush">
        <?php
          $settingsStmt = $pdo->query("SELECT setting_key, setting_value FROM demon_chat_settings");
          $settings = $settingsStmt->fetchAll(PDO::FETCH_ASSOC);
          if ($settings):
            foreach ($settings as $s): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= htmlspecialchars($s['setting_key']) ?></span>
                <span><?= htmlspecialchars($s['setting_value']) ?></span>
              </li>
            <?php endforeach;
          else: ?>
            <li class="list-group-item">No chat settings found.</li>
        <?php endif; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="row gx-0">
    <!-- ─── Main Chat Column (70%) ─── -->
    <div class="col-lg-8 col-xl-9 position-relative">
      <!-- Pinned Message -->
      <div id="chat-pinned"></div>

      <!-- Chatbox Block Start -->
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Shoutbox</h3>
        </div>
        <div class="block-content">
          <div class="js-chat" id="shoutbox-chat">
            <!-- Messages area -->
            <div class="js-chat-messages" id="message-list">
              <!-- Messages will be appended here via JavaScript -->
            </div>
          </div>
        </div>
      </div>
      <!-- Chatbox Block End -->

      <!-- Typing Indicator -->
      <div id="typing-indicator" class="font-size-sm text-muted mt-2" style="display: none;">
        Someone is typing…
      </div>

      <!-- Input Area with Emoji and Send Button -->
      <div class="mt-3 position-relative">
        <div class="input-group input-group-sm">
          <input
            type="text"
            id="chat-input"
            class="form-control"
            maxlength="200"
            placeholder="Type your message…"
          />
          <button
            type="button"
            id="emoji-toggle"
            class="btn btn-secondary"
            title="Emoji"
          ><i class="fa fa-smile"></i></button>
          <button
            type="button"
            id="send-btn"
            class="btn btn-primary"
            disabled
          >Send</button>
        </div>
        <div id="char-count" class="font-size-xs text-muted mt-1">200 characters remaining</div>
        <div id="emoji-picker"></div>
      </div>

      <!-- Jump & Auto-Scroll Buttons -->
      <button
        id="jump-latest-btn"
        class="btn btn-info btn-sm position-absolute"
        style="display: none; right: 20px; bottom: 120px;"
      >Jump to Latest</button>
      <button
        id="toggle-autoscroll"
        class="btn btn-secondary btn-sm position-absolute"
        style="right: 20px; bottom: 80px;"
      >Pause Auto-Scroll</button>
    </div>

    <!-- ─── Who’s Online Column (30%) ─── -->
    <div id="chat-whos-online" class="col-lg-4 col-xl-3 border-start">
      <div class="p-3 overflow-auto" style="height: 440px;">
        <?php include __DIR__ . '/inc/_global/whos_online.php'; ?>
      </div>
    </div>
  </div>
</div>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<script>
(() => {
  const userId    = <?= json_encode((int)$_SESSION['user_id']) ?>;
  const username  = <?= json_encode($_SESSION['username'] ?? '') ?>;
  const maxLen    = 200;
  const csrfToken = <?= json_encode($_SESSION['chat_csrf_token']) ?>;

  let autoScroll      = true;
  let oldestMessageId = null;
  let typingTimeout   = null;

  const container      = document.querySelector('.js-chat-messages');
  const list           = document.getElementById('message-list');
  const inputBox       = document.getElementById('chat-input');
  const sendBtn        = document.getElementById('send-btn');
  const charCountEl    = document.getElementById('char-count');
  const emojiToggle    = document.getElementById('emoji-toggle');
  const emojiPicker    = document.getElementById('emoji-picker');
  const typingInd      = document.getElementById('typing-indicator');
  const jumpBtn        = document.getElementById('jump-latest-btn');
  const toggleScrollBtn= document.getElementById('toggle-autoscroll');
  const chatPinned     = document.getElementById('chat-pinned');

  // 1) Character count
  function updateCharCount() {
    const remaining = maxLen - inputBox.value.length;
    charCountEl.textContent = remaining + ' characters remaining';
    if (remaining < 0 || inputBox.value.trim() === '') {
      charCountEl.style.color = remaining < 0 ? 'red' : '#666';
      sendBtn.disabled = true;
    } else {
      charCountEl.style.color = '#666';
      sendBtn.disabled = false;
    }
  }
  inputBox.addEventListener('input', updateCharCount);

  // 2) Emoji picker population
  const emojis = ['👍','❤️','😂','🎉','😎','😢','💯','🙌','🔥','🥳'];
  emojis.forEach(e => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'emoji-btn';
    btn.textContent = e;
    btn.addEventListener('click', () => {
      inputBox.value += e;
      updateCharCount();
      inputBox.focus();
    });
    emojiPicker.appendChild(btn);
  });

  // Toggle emoji picker visibility & position it below the toggle button
  emojiToggle.addEventListener('click', () => {
    const isHidden = !emojiPicker.style.display || emojiPicker.style.display === 'none';
    if (isHidden) {
      // Compute position relative to the nearest positioned ancestor (the .position-relative div)
      const containerRect = inputBox.closest('.position-relative').getBoundingClientRect();
      const toggleRect    = emojiToggle.getBoundingClientRect();

      // Calculate top/left relative to that ancestor
      const topOffset  = toggleRect.bottom  - containerRect.top;
      const leftOffset = toggleRect.left    - containerRect.left;

      emojiPicker.style.top    = topOffset + 'px';
      emojiPicker.style.left   = leftOffset + 'px';
      emojiPicker.style.display = 'block';
    } else {
      emojiPicker.style.display = 'none';
    }
  });

  // Hide picker if clicking outside
  document.addEventListener('click', e => {
    if (!emojiPicker.contains(e.target) && !emojiToggle.contains(e.target)) {
      emojiPicker.style.display = 'none';
    }
  });

  // 3) Sanitize & format message content
  function parseMessage(raw) {
    raw = raw.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    raw = raw.replace(/\[b\](.*?)\[\/b\]/gi, '<b>$1</b>');
    raw = raw.replace(/\[i\](.*?)\[\/i\]/gi, '<i>$1</i>');
    raw = raw.replace(/\[u\](.*?)\[\/u\]/gi, '<u>$1</u>');
    raw = raw.replace(/\[url=(https?:\/\/[^\]]+)\](.*?)\[\/url\]/gi,
                      '<a href="$1" target="_blank">$2</a>');
    raw = raw.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
    const shortcodeMap = {
      ':smile:': '😄',
      ':laugh:': '😂',
      ':thumbsup:': '👍',
      ':heart:': '❤️'
    };
    Object.keys(shortcodeMap).forEach(code => {
      raw = raw.replace(new RegExp(code, 'g'), shortcodeMap[code]);
    });
    return raw;
  }

  // 4) Render one message line
  function renderMessage(msg, prepend = false) {
    if (document.getElementById('msg-' + msg.id)) return;
    const line = document.createElement('div');
    line.id = 'msg-' + msg.id;
    line.className = 'js-chat-message row push py-2 fadeIn';

    // Avatar Column
    const avatarCol = document.createElement('div');
    avatarCol.className = 'col-auto';
    const avatarImg = document.createElement('img');
    avatarImg.className = 'img-avatar img-avatar32';
    avatarImg.src = msg.avatar_url || 'assets/media/avatars/avatar_placeholder.jpg';
    avatarImg.alt = msg.username + '’s Avatar';
    avatarCol.appendChild(avatarImg);

    // Content Column
    const contentCol = document.createElement('div');
    contentCol.className = 'col' + (msg.user_id === userId ? ' text-right' : '');
    const headerDiv = document.createElement('div');
    const userSpan = document.createElement('span');
    userSpan.className = 'font-w600';
    userSpan.textContent = (msg.user_id === userId ? 'You' : msg.username);
    const timeSpan = document.createElement('span');
    timeSpan.className = 'text-muted font-size-xs ml-2';
    const dt = new Date(msg.created_at);
    timeSpan.textContent =
      dt.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' })
      + ' ' +
      dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    headerDiv.appendChild(userSpan);
    headerDiv.appendChild(timeSpan);

    const textDiv = document.createElement('div');
    textDiv.className = 'mt-1';
    textDiv.innerHTML = parseMessage(msg.content);

    contentCol.appendChild(headerDiv);
    contentCol.appendChild(textDiv);

    // Assemble line
    line.appendChild(avatarCol);
    line.appendChild(contentCol);

    if (prepend) {
      list.insertBefore(line, list.firstChild);
    } else {
      list.appendChild(line);
    }
  }

  // 5) Fetch & render messages periodically
  async function fetchMessages() {
    try {
      const res = await fetch('inc/_global/chatbox/chat_fetch.php?limit=50');
      const data = await res.json();
      if (data.error) { console.error(data.error); return; }

      // Handle pinned message
      if (data.pinned && data.pinned.length) {
        const latestPinned = data.pinned[data.pinned.length - 1];
        chatPinned.style.display = 'block';
        chatPinned.innerHTML = `<strong>[Pinned]</strong> ${parseMessage(latestPinned.content)}`;
      }

      // Render non-pinned messages
      data.messages.forEach(m => {
        renderMessage(m, false);
      });
      if (!oldestMessageId && data.messages.length) {
        oldestMessageId = data.messages[0].id;
      }

      // Auto-scroll if enabled
      if (autoScroll) {
        container.scrollTop = container.scrollHeight;
      }

      // Typing indicator
      if (data.typing && Array.isArray(data.typing)) {
        const othersTyping = data.typing.filter(u => u.id !== userId);
        if (othersTyping.length) {
          typingInd.style.display = 'block';
          typingInd.textContent = `${othersTyping.map(u => u.username).join(', ')} is typing…`;
        } else {
          typingInd.style.display = 'none';
        }
      }
    } catch (err) {
      console.error('Error fetching messages:', err);
    }
  }
  fetchMessages();
  setInterval(fetchMessages, 2000);

  // 6) Send a new message
  sendBtn.addEventListener('click', async () => {
    const content = inputBox.value.trim();
    if (!content || content.length > maxLen) return;

    try {
      const formData = new FormData();
      formData.append('content', content);
      formData.append('csrf_token', csrfToken);

      const res = await fetch('inc/_global/chatbox/chat_post.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.error) {
        alert(data.error);
        return;
      }
      const msg = data.message;
      renderMessage(msg, false);
      inputBox.value = '';
      updateCharCount();
      if (autoScroll) container.scrollTop = container.scrollHeight;
    } catch (err) {
      console.error('Error sending message:', err);
    }
  });

  // 7) Typing indicator ping
  inputBox.addEventListener('input', () => {
    if (typingTimeout) clearTimeout(typingTimeout);
    fetch('inc/_global/chatbox/chat_typing.php', { method: 'POST' }).catch(console.error);
    typingTimeout = setTimeout(() => {
      clearTimeout(typingTimeout);
      typingTimeout = null;
    }, 3000);
  });

  // 8) Auto-scroll and Jump to Latest
  container.addEventListener('scroll', () => {
    const atBottom = (container.scrollTop + container.clientHeight >= container.scrollHeight - 5);
    if (atBottom) {
      jumpBtn.style.display = 'none';
      if (!autoScroll) {
        autoScroll = true;
        toggleScrollBtn.textContent = 'Pause Auto-Scroll';
      }
    } else {
      jumpBtn.style.display = 'block';
    }
  });
  jumpBtn.addEventListener('click', () => {
    container.scrollTop = container.scrollHeight;
    jumpBtn.style.display = 'none';
  });
  toggleScrollBtn.addEventListener('click', () => {
    autoScroll = !autoScroll;
    toggleScrollBtn.textContent = autoScroll ? 'Pause Auto-Scroll' : 'Resume Auto-Scroll';
    if (autoScroll) container.scrollTop = container.scrollHeight;
  });
})();
</script>

<?php require 'inc/_global/views/footer_end.php'; ?>
