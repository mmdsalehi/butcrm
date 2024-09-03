<?php
function crm_report_page() {
    // فرض: تابعی برای دریافت اطلاعات مشتریان از پایگاه داده یا افزونه بوکلی
    $customers = crm_get_customers_data();

    ?>
    <div class="wrap">
        <h1>گزارش پیگیری مشتریان</h1>
        <table id="follow-up-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>نام مشتری</th>
                    <th>نام سرویس</th>
                    <th>تاریخ سرویس</th>
                    <th>سرویس‌های پیشنهادی</th>
                    <th>روزهای باقی‌مانده</th>
                    <th>ارسال پیامک</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo esc_html($customer->name); ?></td>
                    <td><?php echo esc_html($customer->service_name); ?></td>
                    <td><?php echo esc_html($customer->service_date); ?></td>
                    <td><?php echo esc_html($customer->suggested_services); ?></td>
                    <td><?php echo esc_html($customer->days_remaining); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="customer_mobile" value="<?php echo esc_attr($customer->mobile); ?>">
                            <label for="message">پیامک:</label>
                            <textarea name="message" id="message" rows="5" cols="40"></textarea>
                            
                            <!-- دکمه باز کردن پاپ‌آپ atisms -->
                            <button type="button" onclick="openSMSPopup('<?php echo esc_attr($customer->mobile); ?>')">انتخاب از پیام‌های ذخیره‌شده</button>

                            <?php submit_button('ارسال پیامک', 'primary', 'send_sms'); ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function openSMSPopup(number) {
            // فراخوانی پاپ‌آپ موجود در افزونه atisms
            window.openSMSPopup(number);
        }
    </script>

    <?php

    // پردازش ارسال پیامک
    if (isset($_POST['send_sms'])) {
        $customer_mobile = sanitize_text_field($_POST['customer_mobile']);
        $message = sanitize_textarea_field($_POST['message']);
        $current_user = wp_get_current_user()->user_login;

        // استفاده از تابع crm_send_sms_to_customer برای ارسال پیامک
        $result = crm_send_sms_to_customer($customer_mobile, $message, $current_user);

        if ($result && isset($result['Value']) && $result['Value'] > 0) {
            echo '<div class="notice notice-success is-dismissible"><p>پیامک با موفقیت ارسال شد.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>خطا در ارسال پیامک: ' . esc_html($result['RetStatus']) . '</p></div>';
        }
    }
}

function crm_send_sms_to_customer($customer_mobile, $message, $sender_user) {
    if (function_exists('atisms_send_sms')) {
        return atisms_send_sms($customer_mobile, $message, $sender_user);
    } else {
        return array('Value' => 0, 'RetStatus' => 'تابع ارسال پیامک موجود نیست.');
    }
}

// فرض: تابعی برای دریافت اطلاعات مشتریان از پایگاه داده یا افزونه بوکلی
function crm_get_customers_data() {
    global $wpdb;
    // این تابع باید داده‌های مشتریان را از پایگاه داده بگیرد.
    // اینجا به عنوان نمونه چند داده فرضی استفاده می‌شود.
    return [
        (object)[
            'name' => 'محمد رضایی',
            'mobile' => '09123456789',
            'service_name' => 'خدمات پوست',
            'service_date' => '2023-08-01',
            'suggested_services' => 'ماساژ صورت',
            'days_remaining' => 10,
        ],
        (object)[
            'name' => 'سارا احمدی',
            'mobile' => '09198765432',
            'service_name' => 'خدمات مو',
            'service_date' => '2023-07-15',
            'suggested_services' => 'کراتینه مو',
            'days_remaining' => 5,
        ]
    ];
}
?>
