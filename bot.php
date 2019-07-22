<?php

error_reporting(0);

require "config.php";
require "constants.php";
require "functions.php";

# Initialize database
# -------------------------------------------------------------------
$connection = new mysqli($db_servername, $db_username, $db_password, $db_name);
if ($connection->connect_error) {
    var_dump($connection->connect_error);
    return;
}
createTables();
# -------------------------------------------------------------------

# Objects
# -------------------------------------------------------------------
$updates                   = json_decode(file_get_contents("php://input"));
$from_id                   = $updates->message->from->id;
$chat_id                   = $updates->message->chat->id;
$username                  = $updates->message->chat->username;
$first_name                = $updates->message->chat->first_name;
$last_name                 = $updates->message->chat->last_name;
$message_id                = $updates->message->message_id;
$text                      = $updates->message->text;
$date                      = $updates->message->date;
$caption                   = $updates->message->caption;
$sticker                   = $updates->message->sticker;
$video                     = $updates->message->video;
$video_note                = $updates->message->video_note;
$photo                     = $updates->message->photo;
$audio                     = $updates->message->audio;
$voice                     = $updates->message->voice;
$document                  = $updates->message->document;
$forward_date              = $updates->message->forward_date;
$reply                     = $updates->message->reply_to_message;
$reply_text                = $updates->message->reply_to_message->text;
$reply_forward_caption     = $updates->message->reply_to_message->caption;
$reply_forward_date        = $updates->message->reply_to_message->forward_date;
$reply_forward_sender_name = $updates->message->reply_to_message->forward_sender_name;
$reply_forward_first_name  = $updates->message->reply_to_message->forward_from->first_name;
$reply_forward_last_name   = $updates->message->reply_to_message->forward_from->last_name;
$reply_forward_title       = $updates->message->reply_to_message->forward_from_chat->title;
$forward_from              = $updates->message->forward_from;
$forward_first_name        = $updates->message->forward_from->first_name;
$forward_last_name         = $updates->message->forward_from->last_name;
$forward_from_chat         = $updates->message->forward_from_chat;
$forward_from_chat_title   = $updates->message->forward_from_chat->title;
$callback_data             = $updates->callback_query->data;
$callback_chat_id          = $updates->callback_query->message->chat->id;
$callback_message_id       = $updates->callback_query->message->message_id;
# -------------------------------------------------------------------

if (isAdmin($chat_id) || (isset($callback_chat_id) && isAdmin($callback_chat_id))) {
    if ($text == "/start") {
        apiRequest("sendMessage", array(
            "chat_id" => $chat_id,
            "text" => $adminWelcomeText,
            "reply_markup" => json_encode($mainKeyboard)
        ));
    }
    $queryResult = $connection->query("SELECT * FROM works WHERE chat_id='$chat_id'");
    if ($queryResult->num_rows != 0) {
        $record = $queryResult->fetch_assoc();
        if ($record["work"] == "sendToAll") {
            $queryResult = $connection->query("SELECT * FROM users");
            while ($row = $queryResult->fetch_assoc()) {
                apiRequest("forwardMessage", array(
                    "chat_id" => $row["chat_id"],
                    "from_chat_id" => $chat_id,
                    "message_id" => $message_id
                ));
            }
            apiRequest("sendMessage", array(
                "chat_id" => $chat_id,
                "reply_to_message_id" => $message_id,
                "text" => $adminNotifySend
            ));
            $connection->query("DELETE FROM works WHERE chat_id=$chat_id");
        }
    }
    if ($text == "🗣ارسال همگانی") {
        apiRequest("sendMessage", array(
            "chat_id" => $chat_id,
            "reply_to_message_id" => $message_id,
            "text" => $sendToAll
        ));
        $queryResult = $connection->query("SELECT * FROM works");
        if ($queryResult->num_rows == 0) {
            $connection->query("INSERT INTO works (chat_id, work) VALUES ('$chat_id', 'sendToAll')");
        } else {
            $connection->query("UPDATE works SET work='sendToAll' WHERE chat_id=$chat_id");
        }
    }
    if ($text == "📊آمار ربات") {
        apiRequest("sendMessage", array(
            "chat_id" => $chat_id,
            "reply_to_message_id" => $message_id,
            "text" => $selectOption,
            "reply_markup" => json_encode($statisticsKeyboard)
        ));
    }
    if ($text == "👥لیست اعضا") {
        $queryResult    = $connection->query("SELECT * FROM users");
        $lastUsersQuery = $connection->query("SELECT * FROM users ORDER BY id DESC LIMIT 10;");
        $lastUsers      = "";
        while ($row = $lastUsersQuery->fetch_assoc()) {
            $lastUsers .= "[" . $row["display_name"] . "](tg://user?id=" . $row["chat_id"] . ")\r\n";
        }
        if ($queryResult->num_rows == 0) {
            $finalMessage = "عضوی وجود ندارد.";
        } else {
            $finalMessage = "👥تعداد اعضا: " . $queryResult->num_rows . "\r\n\r\nلیست کاربران اخیر:\r\n$lastUsers";
        }
        apiRequest("sendMessage", array(
            "chat_id" => $chat_id,
            "reply_to_message_id" => $message_id,
            "parse_mode" => "markdown",
            "text" => $finalMessage,
            "reply_markup" => json_encode($fullUsersListKeyboard)
        ));
    }
    if ($text == "⛔️لیست سیاه") {
        $queryResult    = $connection->query("SELECT * FROM banlist");
        $lastUsersQuery = $connection->query("SELECT * FROM banlist ORDER BY id DESC LIMIT 10;");
        $lastUsers      = "";
        while ($row = $lastUsersQuery->fetch_assoc()) {
            $lastUsers .= "[" . $row["display_name"] . "](tg://user?id=" . $row["chat_id"] . ")\r\n";
        }
        if ($queryResult->num_rows == 0) {
            $finalMessage = "کاربری در لیست سیاه وجود ندارد.";
        } else {
            $finalMessage = "👥تعداد کاربران در لیست سیاه: " . $queryResult->num_rows . "\r\n\r\nلیست کاربران اخیر:\r\n$lastUsers";
        }
        apiRequest("sendMessage", array(
            "chat_id" => $chat_id,
            "reply_to_message_id" => $message_id,
            "parse_mode" => "markdown",
            "text" => $finalMessage
        ));
    }
    if ($text == "🔙بازگشت") {
        apiRequest("sendMessage", array(
            "chat_id" => $chat_id,
            "reply_to_message_id" => $message_id,
            "text" => $backOperation,
            "reply_markup" => json_encode($mainKeyboard)
        ));
    }
    if ($callback_data == "getFullUsersList") {
        $users       = array();
        $queryResult = $connection->query("SELECT * FROM users");
        if ($queryResult->num_rows > 0) {
            while ($row = $queryResult->fetch_assoc()) {
                $users[] = $row;
            }
            $finalMessage = "👥تعداد اعضا: " . $queryResult->num_rows;
        } else {
            $finalMessage = "عضوی وجود ندارد.";
        }
        file_put_contents("users.json", json_encode($users));
        apiRequest("sendDocument", array(
            "chat_id" => $callback_chat_id,
            "caption" => $finalMessage,
            "document" => new CURLFile("users.json")
        ));
        if (file_exists("users.json"))
            unlink("users.json");
    }
    if (isset($reply)) {
        $reply_forward_sender_name = $reply_forward_sender_name == "" ? $reply_forward_last_name != "" ? $reply_forward_first_name . " " . $reply_forward_last_name : $reply_forward_first_name : $reply_forward_sender_name;
        $reply_forward_sender_name = $reply_forward_sender_name == "" ? $reply_forward_title : $reply_forward_sender_name;
        $reply_text                = $reply_text == "" ? $reply_forward_caption : $reply_text;
        $queryResult               = $connection->query("SELECT * FROM messages WHERE name='$reply_forward_sender_name' AND date='$reply_forward_date' AND text='$reply_text' LIMIT 1");
        $record                    = $queryResult->fetch_assoc();
        if ($text == "/ban") {
            $connection->query("INSERT INTO banlist(chat_id, display_name) VALUES ('" . $record["chat_id"] . "', '" . $record["name"] . "')");
            apiRequest("sendMessage", array(
                "chat_id" => $chat_id,
                "reply_to_message_id" => $message_id,
                "parse_mode" => "markdown",
                "text" => "⛔️کاربر [$reply_forward_sender_name](tg://user?id=" . $record["chat_id"] . ") به لیست سیاه افزوده شد."
            ));
        } else if ($text == "/unban") {
            $connection->query("DELETE FROM banlist WHERE chat_id=" . $record["chat_id"]);
            apiRequest("sendMessage", array(
                "chat_id" => $chat_id,
                "reply_to_message_id" => $message_id,
                "parse_mode" => "markdown",
                "text" => "⛔️کاربر [$reply_forward_sender_name](tg://user?id=" . $record["chat_id"] . ") از لیست سیاه خارج شد."
            ));
        } else {
            if (isset($sticker)) {
                apiRequest("sendSticker", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "sticker" => $sticker->file_id
                ));
            } else if (isset($video)) {
                apiRequest("sendVideo", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "video" => $video->file_id,
                    "caption" => $caption
                ));
            } else if (isset($photo)) {
                $file_id = "";
                for ($i = 0; $i <= 10; $i++) {
                    $file_id = $photo[$i]->file_id;
                    if (empty($file_id)) {
                        $file_id = $photo[$i - 1]->file_id;
                        break;
                    }
                }
                apiRequest("sendPhoto", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "photo" => $file_id,
                    "caption" => $caption
                ));
            } else if (isset($video_note)) {
                apiRequest("sendVideoNote", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "video_note" => $video_note->file_id
                ));
            } else if (isset($audio)) {
                apiRequest("sendAudio", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "audio" => $audio->file_id,
                    "caption" => $caption
                ));
            } else if (isset($voice)) {
                apiRequest("sendVoice", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "voice" => $voice->file_id,
                    "caption" => $caption
                ));
            } else if (isset($document)) {
                apiRequest("sendDocument", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "document" => $document->file_id,
                    "caption" => $caption
                ));
            } else {
                apiRequest("sendMessage", array(
                    "chat_id" => $record["chat_id"],
                    "reply_to_message_id" => $record["message_id"],
                    "text" => $text
                ));
            }
            apiRequest("sendMessage", array(
                "chat_id" => $chat_id,
                "reply_to_message_id" => $message_id,
                "text" => $adminNotifySend
            ));
        }
    }
} else {
    if ($text == "/start") {
        apiRequest("sendMessage", array(
            "chat_id" => $chat_id,
            "text" => $userWelcomeText
        ));
        $queryResult = $connection->query("SELECT * FROM users WHERE chat_id='$chat_id'");
        if ($queryResult->num_rows == 0) {
            $name = $last_name != "" ? $first_name . " " . $last_name : $first_name;
            $date = date("Y/m/d H:i:s", $date);
            $connection->query("INSERT INTO users(chat_id, display_name, username, joined_date) VALUES ('$chat_id', '$name', '$username', '$date')");
        }
    } else {
        $queryResult = $connection->query("SELECT * FROM banlist WHERE chat_id='$chat_id'");
        if ($queryResult->num_rows == 0) {
            $name = $last_name != "" ? $first_name . " " . $last_name : $first_name;
            $name = isset($forward_from_chat) ? $forward_from_chat_title : $name;
            $name = isset($forward_from) ? $forward_last_name != "" ? $forward_first_name . " " . $forward_last_name : $forward_first_name : $name;
            $date = isset($forward_from_chat) ? $forward_date : $date;
            $date = isset($forward_from) ? $forward_date : $date;
            $text = $text == "" ? $caption : $text;
            $connection->query("INSERT INTO messages(chat_id, message_id, date, name, text) VALUES ('$chat_id', '$message_id', '$date', '$name', '$text')");
            if (isset($forward_from) || isset($forward_from_chat)) {
                foreach ($admins as $admin) {
                    $forwardRequest = json_decode(json_encode(apiRequest("forwardMessage", array(
                        "chat_id" => $admin,
                        "from_chat_id" => $chat_id,
                        "message_id" => $message_id
                    ))));
                    apiRequest("sendMessage", array(
                        "chat_id" => $admin,
                        "reply_to_message_id" => $forwardRequest->result->message_id,
                        "parse_mode" => "markdown",
                        "text" => "این پیام توسط  [$first_name $last_name](tg://user?id=$chat_id) فروارد شده است."
                    ));
                }
            } else {
                foreach ($admins as $admin) {
                    apiRequest("forwardMessage", array(
                        "chat_id" => $admin,
                        "from_chat_id" => $chat_id,
                        "message_id" => $message_id
                    ));
                }
            }
            apiRequest("sendMessage", array(
                "chat_id" => $chat_id,
                "reply_to_message_id" => $message_id,
                "text" => $userNotifySend
            ));
        } else {
            apiRequest("sendMessage", array(
                "chat_id" => $chat_id,
                "reply_to_message_id" => $message_id,
                "text" => $banlistError
            ));
        }
    }
}
//file_put_contents("last", json_encode($updates)); // save temporary last updates for debugging
