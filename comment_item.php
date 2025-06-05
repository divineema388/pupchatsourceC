<?php
// Fetch replies for this comment
$replies_query = $conn->prepare("
    SELECT c.*, u.username, u.profile_pic, u.verified
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.parent_id = ?
    ORDER BY c.created_at ASC
");
$replies_query->bind_param("i", $comment['id']);
$replies_query->execute();
$replies = $replies_query->get_result();

// Check if current user is the comment owner
$is_owner = ($comment['user_id'] == $user_id);
?>

<div class="comment-container" id="comment-<?php echo $comment['id']; ?>">
    <div class="comment-content">
        <div class="comment-card">
            <div class="comment-header">
                <img src="<?php echo htmlspecialchars($comment['profile_pic']); ?>" class="comment-profile-img">
                <div>
                    <span class="comment-username">
                        <?php echo htmlspecialchars($comment['username']); ?>
                        <?php if ($comment['verified'] == 1): ?>
                            <img src="media/veri.png" width="14" height="14" alt="Verified">
                        <?php endif; ?>
                    </span>
                    <span class="comment-time"><?php echo date('M j, g:i a', strtotime($comment['created_at'])); ?></span>
                </div>
            </div>
            
            <p class="comment-text mb-2"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
            
            <div class="comment-actions">
                <span class="comment-action" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">
                    Reply
                </span>
            </div>
            
            <!-- Reply Form (hidden by default) -->
            <form method="POST" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;" class="mt-2">
                <div class="d-flex align-items-center">
                    <img src="<?php echo $profile_pic; ?>" class="comment-profile-img me-2">
                    <textarea name="content" class="form-control flex-grow-1" placeholder="Write a reply..." rows="1" style="border-radius: 20px; padding: 8px 15px;" required></textarea>
                    <input type="hidden" name="new_comment" value="1">
                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" class="btn btn-primary btn-sm ms-2" style="border-radius: 20px;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
            
            <!-- Replies -->
            <div class="replies mt-2">
                <?php while ($reply = $replies->fetch_assoc()): ?>
                    <div class="reply-card">
                        <?php 
                        // Recursively include the same template for replies
                        $comment = $reply;
                        include 'comment_item.php'; 
                        ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <?php if ($is_owner): ?>
    <div class="delete-sidebar">
        <form method="POST" class="delete-form">
            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
            <button type="submit" name="delete_comment" class="delete-btn">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>