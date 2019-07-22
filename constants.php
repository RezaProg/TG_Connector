<?php
$adminWelcomeText      = "🔸سلام مدیر گرامی،\r\n🔹به پنل مدیریت ربات خوش آمدید.";
$userWelcomeText       = "🔸سلام دوست عزیز به ربات پیام رسان RezaProg خوش آمدید؛\r\n🔹لطفا پیام خود را ارسال کنید، در اسرع وقت به ما پاسخ خواهم داد.";
$adminNotifySend       = "✅پیغام شما ارسال شد.";
$userNotifySend        = "✅پیغام شما ارسال شد، لطفا منتظر دریافت پاسخ باشید.";
$sendToAll             = "🔸پیام خود را ارسال کنید:";
$selectOption          = "🔸یک گزینه را انتخاب نمایید:";
$backOperation         = "🔙به منوی اصلی بازگشتید:";
$banlistError          = "⛔️شما در لیست سیاه قرار دارید.";
$mainKeyboard          = array(
    "keyboard" => array(
        array(
            array(
                "text" => "📊آمار ربات"
            ),
            array(
                "text" => "🗣ارسال همگانی"
            )
        )
    ),
    "resize_keyboard" => true
);
$statisticsKeyboard    = array(
    "keyboard" => array(
        array(
            array(
                "text" => "⛔️لیست سیاه"
            ),
            array(
                "text" => "👥لیست اعضا"
            )
        ),
        array(
            array(
                "text" => "🔙بازگشت"
            )
        )
    ),
    "resize_keyboard" => true
);
$fullUsersListKeyboard = array(
    'inline_keyboard' => array(
        array(
            array(
                "text" => "📃دریافت لیست کامل اعضا",
                'callback_data' => "getFullUsersList"
            )
        )
    )
);
