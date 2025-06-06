<?php

/**

 * footer_start.php

 *

 * Author: pixelcave

 *

 * All vital JS scripts are included here

 *

 */

?>



<!--

    Codebase JS



    Core libraries and functionality

    webpack is putting everything together at assets/_js/main/app.js

-->

<script src="<?php echo $cb->assets_folder; ?>/js/codebase.app.min.js"></script>
<script src="<?php echo $cb->assets_folder; ?>/js/lib/jquery.min.js"></script>

<script>
  $(document).ready(function() {
    // Single “Mark as Read”
    $('#notif-list').on('click', '.mark-read-single', function(e) {
      e.preventDefault();
      e.stopPropagation(); // <<< Prevent dropdown from closing
      var btn    = $(this);
      var notifId = btn.data('id');

      $.post('/inc/_global/mark_notification.php', {
        action:   'single',
        notif_id: notifId
      }, function(resp) {
        if (resp.success) {
          var li = btn.closest('li[data-id="' + notifId + '"]');
          li.find('i.fa-circle').removeClass('text-primary').addClass('text-muted');
          btn.remove();

          var badge = $('#notif-badge');
          var cnt   = parseInt(badge.text()) || 0;
          cnt = Math.max(0, cnt - 1);
          if (cnt === 0) badge.remove();
          else           badge.text(cnt);
        }
      }, 'json').fail(function(xhr, status, err) {
        console.error('Mark single AJAX error:', status, err);
      });
    });

    // “Mark All as Read”
    $('#mark-all-read').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation(); // <<< Prevent dropdown from closing

      $.post('/inc/_global/mark_notification.php', {
        action: 'all'
      }, function(resp) {
        if (resp.success) {
          $('#notif-list li').each(function() {
            var li = $(this);
            li.find('i.fa-circle').removeClass('text-primary').addClass('text-muted');
            li.find('.mark-read-single').remove();
          });
          $('#notif-badge').remove();
        }
      }, 'json').fail(function(xhr, status, err) {
        console.error('Mark all AJAX error:', status, err);
      });
    });
  });
</script>
