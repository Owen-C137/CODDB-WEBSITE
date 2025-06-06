<?php
// spin.php  /root
// -------
// A “Spin the Wheel” page wrapped in the site’s global header/sidebar/footer.

require 'inc/_global/config.php';
require 'inc/_global/login_check.php'; // ensures session_start(), $pdo, $_SESSION['user_id']
require 'inc/backend/config.php';      // if you need $cb or other settings

$userId = (int)($_SESSION['user_id'] ?? 0);

// 0) Fetch current spin_allowance
$allowStmt = $pdo->prepare("
    SELECT spin_allowance
    FROM demon_users
    WHERE id = :uid
    LIMIT 1
");
$allowStmt->execute(['uid' => $userId]);
$row = $allowStmt->fetch(PDO::FETCH_ASSOC);
$spinAllowance = (int)($row['spin_allowance'] ?? 0);

// 1) Fetch wheel segments from the database
$segStmt = $pdo->query("
    SELECT id, label, value
    FROM demon_spin_segments
    ORDER BY weight ASC, id ASC
");
$segments = $segStmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Fetch this user’s recent spin history (last 10)
$myHistStmt = $pdo->prepare("
    SELECT h.prize_key, h.reward_amount, h.created_at
    FROM demon_spin_history h
    WHERE h.user_id = :uid
    ORDER BY h.created_at DESC
    LIMIT 10
");
$myHistStmt->execute(['uid' => $userId]);
$userHistory = $myHistStmt->fetchAll(PDO::FETCH_ASSOC);

// 3) Fetch everyone’s recent spin history (last 10), joining to get username
$allHistStmt = $pdo->query("
    SELECT h.user_id, u.username, h.prize_key, h.reward_amount, h.created_at
    FROM demon_spin_history h
    LEFT JOIN demon_users u ON h.user_id = u.id
    ORDER BY h.created_at DESC
    LIMIT 10
");
$allHistory = $allHistStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'inc/_global/views/head_start.php'; ?>

    <!-- ============================
         |   PAGE-SPECIFIC STYLES   |
         ============================ -->
    <style>
      /* Container for the wheel + outer light border */
      .wheel-wrapper {
        position: relative;
        width: 450px;
        height: 450px;
        margin: 0 auto;
      }
      /* The circular “border” behind the canvas where we put lights */
      .wheel-border {
        position: absolute;
        top: 0;
        left: 0;
        width: 450px;
        height: 450px;
        border-radius: 50%;
        background: radial-gradient(circle at center, #2c2f48, #1b1d2e);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
      }
      /* The actual wheel-drawing canvas, inset slightly from the border */
      .wheel-canvas {
        position: absolute;
        top: 15px;
        left: 15px;
        width: 420px;
        height: 420px;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        transition: transform 5s cubic-bezier(0.33,1,0.68,1);
      }
      /* Small “light bulb” circles around the wheel border */
      .wheel-border .light {
        position: absolute;
        width: 18px;
        height: 18px;
        background: #ffeb3b;
        border-radius: 50%;
        box-shadow: 0 0 8px rgba(255, 235, 59, 0.8);
      }
      /* 12 lights, positioned around the circle */
      .light:nth-child(1)  { top:  0%; left: 50%; transform: translate(-50%,-50%); }
      .light:nth-child(2)  { top: 13%; left: 82%; transform: translate(-50%,-50%); }
      .light:nth-child(3)  { top: 37%; left: 97%; transform: translate(-50%,-50%); }
      .light:nth-child(4)  { top: 63%; left: 97%; transform: translate(-50%,-50%); }
      .light:nth-child(5)  { top: 87%; left: 82%; transform: translate(-50%,-50%); }
      .light:nth-child(6)  { top:100%; left: 50%; transform: translate(-50%,-50%); }
      .light:nth-child(7)  { top: 87%; left: 18%; transform: translate(-50%,-50%); }
      .light:nth-child(8)  { top: 63%; left:  3%; transform: translate(-50%,-50%); }
      .light:nth-child(9)  { top: 37%; left:  3%; transform: translate(-50%,-50%); }
      .light:nth-child(10) { top: 13%; left: 18%; transform: translate(-50%,-50%); }
      .light:nth-child(11) { top:  0%; left: 50%; transform: translate(-50%,-50%); } /* duplicate for symmetry */
      .light:nth-child(12) { top: 13%; left: 82%; transform: translate(-50%,-50%); } /* duplicate for symmetry */

      /* The pointer (triangle) at the top of the wheel */
      .pointer {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        margin-left: -12px;
        margin-top: -250px; /* exactly position tip at container top */
        border-left: 12px solid transparent;
        border-right: 12px solid transparent;
        border-top: 20px solid #dc3545;
        z-index: 10;
      }

      /* Style overrides for the tables */
      .table thead {
        background-color: #343a40;
        color: #fff;
      }
      .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
      }
      .table-responsive {
        overflow-x: auto;
      }
    </style>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<div class="content">
      <img
      src="/assets/media/spin_to_win.png"
      alt="Spin to Win"
      class="img-fluid"
      style="max-width: 100%; height: auto;"
    />

  <!-- Display remaining spins -->
  <div class="text-center mb-4">
    <strong>Remaining Spins:</strong>
    <span id="remaining-spins"><?= $spinAllowance; ?></span>
  </div>

  <div class="wheel-wrapper">
    <!-- Outer circle for lights/background -->
    <div class="wheel-border">
      <!-- 12 “light bulb” divs -->
      <?php for ($i = 0; $i < 12; $i++): ?>
        <div class="light"></div>
      <?php endfor; ?>
    </div>

    <!-- Actual wheel canvas (slightly smaller) -->
    <canvas
      id="wheelcanvas"
      class="wheel-canvas"
      width="420" height="420"
      style="transform: rotate(0deg);"
    ></canvas>

    <!-- Pointer: The red triangle that “points” to the winning segment -->
    <div class="pointer"></div>
  </div>

  <button id="spin-btn" class="btn btn-lg btn-primary d-block mx-auto my-4">
    Spin Now!
    <span id="spin-loading" class="spinner-border spinner-border-sm ml-2" role="status" aria-hidden="true" style="display: none;"></span>
  </button>
  <p class="text-center text-muted">
    You can spin once per day (unless you have extra spins).
  </p>

  <!-- User’s own recent spin history -->
  <div class="mt-5">
    <h5 class="text-center">Your Recent Spins</h5>
    <?php if (count($userHistory) === 0): ?>
      <p class="text-center text-muted">No spins yet.</p>
    <?php else: ?>
      <div class="table-responsive mx-auto" style="width:80%;">
        <table class="table table-striped table-bordered table-hover mb-0">
          <thead>
            <tr>
              <th>Date</th>
              <th>Prize Key</th>
              <th>Reward</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($userHistory as $h): ?>
              <tr>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($h['created_at']))) ?></td>
                <td><?= htmlspecialchars($h['prize_key']) ?></td>
                <td><?= intval($h['reward_amount']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Everyone’s recent spin history -->
  <div class="mt-5">
    <h5 class="text-center">Everyone’s Recent Spins</h5>
    <?php if (count($allHistory) === 0): ?>
      <p class="text-center text-muted">No spins recorded.</p>
    <?php else: ?>
      <div class="table-responsive mx-auto" style="width:90%;">
        <table class="table table-striped table-bordered table-hover mb-0">
          <thead>
            <tr>
              <th>User</th>
              <th>Date</th>
              <th>Prize Key</th>
              <th>Reward</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allHistory as $h): ?>
              <tr>
                <td><?= htmlspecialchars($h['username'] ?? 'User#'.intval($h['user_id'])) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($h['created_at']))) ?></td>
                <td><?= htmlspecialchars($h['prize_key']) ?></td>
                <td><?= intval($h['reward_amount']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- ============================
     |   DEPENDENCY INCLUDES    |
     ============================ -->
<!-- SweetAlert2 for prettier modals/toasts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Canvas-Confetti for win/lose effects -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

<script>
  // ============================
  // 1) LOAD SEGMENTS FROM PHP INTO JS
  // ============================
  const segments = <?= json_encode($segments, JSON_HEX_TAG); ?>;
  const canvas   = document.getElementById('wheelcanvas');
  const ctx      = canvas.getContext('2d');
  const CX       = canvas.width / 2;
  const CY       = canvas.height / 2;
  const RADIUS   = CX - 10; // leave small inner padding

  // A list of “carnival” colors to cycle through
  const sliceColors = [
    '#FFEB3B', // Bright Yellow
    '#03A9F4', // Light Blue
    '#E91E63', // Pink
    '#9C27B0', // Purple
    '#FF5722', // Deep Orange
    '#4CAF50', // Green
    '#FF9800', // Orange
    '#F44336', // Red
    '#3F51B5', // Indigo
    '#00BCD4', // Cyan
    '#CDDC39', // Lime
    '#F8BBD0'  // Light Pink
  ];

  // Draw the wheel once on page load using multicolored slices
  function drawWheel() {
    const total    = segments.length;
    const anglePer = (2 * Math.PI) / total;

    for (let i = 0; i < total; i++) {
      ctx.beginPath();
      ctx.moveTo(CX, CY);
      ctx.fillStyle = sliceColors[i % sliceColors.length];
      ctx.arc(
        CX,
        CY,
        RADIUS,
        i * anglePer,
        (i + 1) * anglePer
      );
      ctx.closePath();
      ctx.fill();

      // Draw white boundary between slices
      ctx.strokeStyle = '#fff';
      ctx.lineWidth = 2;
      ctx.stroke();

      // Draw label
      ctx.save();
      ctx.translate(CX, CY);
      ctx.rotate(i * anglePer + anglePer / 2);
      ctx.textAlign = 'right';
      ctx.fillStyle = '#000';
      ctx.font = 'bold 16px sans-serif';
      ctx.fillText(segments[i].label, RADIUS - 10, 10);
      ctx.restore();
    }
  }
  drawWheel();

  // ============================
  // 2) SPIN LOGIC
  // ============================
  const spinBtn  = document.getElementById('spin-btn');
  const spinLoad = document.getElementById('spin-loading');
  let isSpinning = false;

  spinBtn.addEventListener('click', function() {
    if (isSpinning) return;
    isSpinning = true;
    spinBtn.disabled = true;
    spinLoad.style.display = 'inline-block';

    fetch('/inc/_global/spin_post.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
    })
    .then(res => res.json())
    .then(data => {
      spinLoad.style.display = 'none';

      if (!data.success) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: data.error || 'Could not spin right now.',
        });
        isSpinning = false;
        spinBtn.disabled = false;
        return;
      }

      const prizeIndex = parseInt(data.prize_index, 10);
      const prizeValue = parseInt(data.prize_value, 10);
      const remaining  = parseInt(data.remaining_spins, 10);

      // Animate to the winning slice
      rotateWheelTo(prizeIndex, prizeValue, remaining);
    })
    .catch(err => {
      console.error(err);
      spinLoad.style.display = 'none';
      Swal.fire({
        icon: 'error',
        title: 'Network Error',
        text: 'Please try again.',
      });
      isSpinning = false;
      spinBtn.disabled = false;
    });
  });

  function rotateWheelTo(index, value, remaining) {
    const total    = segments.length;
    const sliceDeg = 360 / total;
    // Midpoint of slice = (index + 0.5)*sliceDeg
    const targetAngle = (index + 0.5) * sliceDeg;
    // We want that midpoint to end up at “12 o’clock” (i.e. 270 degrees)
    const baseRotation = 270 - targetAngle;
    // Add some full spins (e.g. 4 full revolutions = 4*360)
    const fullSpins = 360 * 4;
    const finalRotation = fullSpins + baseRotation;

    // Apply CSS transform
    canvas.style.transform = `rotate(${finalRotation}deg)`;

    // When the CSS transition ends, show the result
    canvas.addEventListener('transitionend', () => {
      // small delay so user sees the wheel “stop” at the perfect spot
      setTimeout(() => {
        if (value > 0) {
          // 🎉 WIN!
          Swal.fire({
            icon: 'success',
            title: 'Congratulations!',
            html: `You won <strong>${value} credits</strong>!<br>Remaining spins: <strong>${remaining}</strong>`,
            showConfirmButton: false,
            timer: 2000,
            didOpen: () => {
              // Launch confetti for 2 seconds
              const duration = 2 * 1000;
              const end = Date.now() + duration;

              (function frame() {
                // random confetti bursts
                confetti({
                  particleCount: 15,
                  angle: 60,
                  spread: 55,
                  origin: { x: 0 },
                });
                confetti({
                  particleCount: 15,
                  angle: 120,
                  spread: 55,
                  origin: { x: 1 },
                });
                if (Date.now() < end) {
                  requestAnimationFrame(frame);
                }
              })();
            }
          });
        } else {
          // 😢 LOSS
          Swal.fire({
            icon: 'error',
            title: 'Sorry!',
            html: `No reward this time.<br>Remaining spins: <strong>${remaining}</strong>`,
            showConfirmButton: false,
            timer: 2000,
            didOpen: () => {
              // launch a “muted” gray confetti to simulate a “sad” effect
              const duration = 1.5 * 1000;
              const end = Date.now() + duration;
              (function frame() {
                confetti({
                  particleCount: 20,
                  colors: ['#555555', '#888888', '#BBBBBB'],
                  origin: { x: Math.random(), y: Math.random() * 0.6 + 0.2 }
                });
                if (Date.now() < end) {
                  requestAnimationFrame(frame);
                }
              })();
            }
          });
        }

        // Update the “Remaining Spins” display
        document.getElementById('remaining-spins').textContent = remaining;

        // Reset wheel to 0° instantly (no visible jump)
        canvas.style.transition = 'none';
        canvas.style.transform = 'rotate(0deg)';

        // Force reflow so next transition works properly
        canvas.offsetHeight; 
        // Restore the transition property
        canvas.style.transition = 'transform 5s cubic-bezier(0.33,1,0.68,1)';

        // Allow next spin
        isSpinning = false;
        spinBtn.disabled = false;
      }, 100);
    }, { once: true });
  }
</script>

<?php require 'inc/_global/views/footer_end.php'; ?>
