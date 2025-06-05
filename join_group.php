<?php
session_start();
include "db_connect.php";
// Load profanity words from the text file
$profanityWords = file('profanity_words.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function checkProfanity($input, $profanityWords) {
    foreach ($profanityWords as $word) {
        if (stripos($input, $word) !== false) {
            return true;
        }
    }
    return false;
}

if (!isset($_SESSION["user_id"])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION["user_id"];
$userInput = $_POST['message'] ?? '';

if (checkProfanity($userInput, $profanityWords)) {
    // Store the profanity usage in the database
    $stmt = $conn->prepare("INSERT INTO suspended_users (user_id, message, detected_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $userInput);
    $stmt->execute();
    
    // Log out the user and redirect
    $_SESSION['profanity_logout'] = true;
    session_destroy();
    header('Location: sus.html');
    exit();
}
if (!isset($_SESSION["user_id"])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION["user_id"]; // ✅ Fix: Ensure user_id is set
$group_id = isset($_GET["group_id"]) ? $_GET["group_id"] : (isset($_POST["group_id"]) ? $_POST["group_id"] : null);

if (!$group_id) {
    die("Unauthorized access.");
}
// Fetch group details
$group_query = $conn->prepare("SELECT * FROM groups WHERE id = ?");
$group_query->bind_param("i", $group_id);
$group_query->execute();
$group_result = $group_query->get_result();
$group = $group_result->fetch_assoc();

if ($group["is_encrypted"]) {
    // Handle password validation for encrypted groups
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["password"])) {
        $input_password = trim($_POST["password"]);

        // Verify the password
        if (!password_verify($input_password, $group["encrypted_password"])) {
            // Redirect to error page if the password is incorrect
            header("Location: error.html");
            exit();
        }
    }
}

// Check if user is already a member
$check_membership = $conn->prepare("SELECT * FROM group_members WHERE user_id = ? AND group_id = ?");
$check_membership->bind_param("ii", $user_id, $group_id);
$check_membership->execute();
$result = $check_membership->get_result();

if ($result->num_rows == 0) {
    $join_stmt = $conn->prepare("INSERT INTO group_members (user_id, group_id) VALUES (?, ?)");
    $join_stmt->bind_param("ii", $user_id, $group_id);
    $join_stmt->execute();
}

// Fetch group details
$group_query = $conn->prepare("SELECT group_name FROM groups WHERE id = ?");
$group_query->bind_param("i", $group_id);
$group_query->execute();
$group_result = $group_query->get_result();
$group = $group_result->fetch_assoc();

// Fetch previous messages
$message_query = $conn->prepare("
    SELECT group_messages.message, group_messages.audio_path, users.username, group_messages.sent_at 
    FROM group_messages 
    JOIN users ON group_messages.user_id = users.id 
    WHERE group_messages.group_id = ?
    ORDER BY group_messages.sent_at ASC
");
$message_query->bind_param("i", $group_id);
$message_query->execute();
$messages = $message_query->get_result();
// Handle voice note upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["voice_note"])) {
    $target_dir = "audio/";
    $file_name = uniqid() . "_" . basename($_FILES["voice_note"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["voice_note"]["tmp_name"], $target_file)) {
        $send_audio = $conn->prepare("INSERT INTO group_messages (group_id, user_id, audio_path, sent_at) VALUES (?, ?, ?, NOW())");
        $send_audio->bind_param("iis", $group_id, $user_id, $target_file);
        $send_audio->execute();
        header("Location: join_group.php?group_id=$group_id");
        exit();
    }
}
// Handle sending a new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"])) {
    $message = trim($_POST["message"]);
    if (!empty($message)) {
        $send_message = $conn->prepare("INSERT INTO group_messages (group_id, user_id, message, sent_at) VALUES (?, ?, ?, NOW())");
        $send_message->bind_param("iis", $group_id, $user_id, $message);
        $send_message->execute();
        header("Location: join_group.php?group_id=$group_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - <?php echo htmlspecialchars($group["group_name"]); ?></title>

    <!-- External CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        body {
    background: linear-gradient(to right, #667eea, #764ba2);
    font-family: 'Arial', sans-serif;
    color: white;
}

.chat-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    color: black;
}

h2 {
    font-size: 1.8em;
    margin-bottom: 20px;
    text-align: center;
}

.message-box {
    height: 300px;
    overflow-y: auto;
    border-radius: 5px;
    padding: 15px;
    background: #f8f9fa;
    margin-bottom: 20px;
}

.message {
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    max-width: 80%;
}

.message.sent {
    background: #007bff;
    color: white;
    text-align: right;
    margin-left: auto;
}

.message.received {
    background: #e9ecef;
    color: black;
    text-align: left;
    margin-right: auto;
}

.message small {
    display: block;
    font-size: 0.8em;
    color: gray;
}

.send-box {
    display: flex;
    gap: 10px;
}

.send-box input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1em;
}

.send-box input:focus {
    outline: none;
    border-color: #007bff;
}

.send-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 1em;
}

.send-btn:hover {
    background: #0056b3;
}

#audio-preview {
    display: none;
    text-align: center;
    margin-top: 10px;
}

#audio-preview audio {
    width: 100%;
    margin-bottom: 10px;
}

#record-btn {
    background: #28a745;
}

#record-btn:hover {
    background: #218838;
}

.btn-outline-secondary {
    margin-top: 20px;
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
}
    </style>
</head>
<body>

    <div class="chat-container animate__animated animate__fadeIn">
        <h2 class="text-center mb-4"><i class="fa fa-comments"></i> <?php echo htmlspecialchars($group["group_name"]); ?></h2>

        <div class="message-box">
            <?php while ($msg = $messages->fetch_assoc()) { ?>
               <div class="message <?php echo ($msg['username'] === $_SESSION['username']) ? 'sent' : 'received'; ?>">
    <strong><?php echo htmlspecialchars($msg["username"]); ?></strong>
    <?php if ($msg["message"]) { ?>
        <p><?php echo htmlspecialchars($msg["message"]); ?></p>
    <?php } ?>
    <?php if ($msg["audio_path"]) { ?>
        <audio controls>
            <source src="<?php echo htmlspecialchars($msg["audio_path"]); ?>" type="audio/mp3">
            Your browser does not support the audio element.
        </audio>
    <?php } ?>
    <small><i class="fa fa-clock"></i> <?php echo $msg["sent_at"]; ?></small>
</div>
            <?php } ?>
            <?php if ($messages->num_rows == 0) echo "<p class='text-muted text-center'>No messages yet.</p>"; ?>
        </div>

        <form action="" method="POST" class="mt-3">
           <div class="send-box">
    <input type="text" name="message" class="form-control" placeholder="Type your message..." required>
    <button type="button" id="record-btn" class="send-btn"><i class="fa fa-microphone"></i></button>
    <button type="submit" class="send-btn"><i class="fa fa-paper-plane"></i></button>
<div id="audio-preview" style="display: none; text-align: center; margin-top: 10px;">
    <audio id="audio-player" controls></audio>
    <br>
    <button type="button" id="send-voice" class="send-btn"><i class="fa fa-paper-plane"></i> Send</button>
    <button type="button" id="delete-voice" class="send-btn btn-danger"><i class="fa fa-trash"></i> Delete</button>
</div>
        </form>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    let mediaRecorder;
    let audioChunks = [];
    let audioBlob;

    document.getElementById("record-btn").addEventListener("click", async () => {
        if (!mediaRecorder || mediaRecorder.state === "inactive") {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);

            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            };

            mediaRecorder.onstop = () => {
                audioBlob = new Blob(audioChunks, { type: "audio/mp3" });
                const audioURL = URL.createObjectURL(audioBlob);
                
                document.getElementById("audio-player").src = audioURL;
                document.getElementById("audio-preview").style.display = "block";

                audioChunks = [];
            };

            mediaRecorder.start();
            document.getElementById("record-btn").innerHTML = "<i class='fa fa-stop'></i>";
        } else {
            mediaRecorder.stop();
            document.getElementById("record-btn").innerHTML = "<i class='fa fa-microphone'></i>";
        }
    });

    document.getElementById("delete-voice").addEventListener("click", () => {
        document.getElementById("audio-preview").style.display = "none";
        document.getElementById("audio-player").src = "";
        audioBlob = null;
    });

    document.getElementById("send-voice").addEventListener("click", async () => {
        if (!audioBlob) return;

        const file = new File([audioBlob], "voice_note.mp3", { type: "audio/mp3" });
        const formData = new FormData();
        formData.append("voice_note", file);

        await fetch(window.location.href, { method: "POST", body: formData });

        document.getElementById("audio-preview").style.display = "none";
        window.location.reload();
    });
</script>
</body>
</html>