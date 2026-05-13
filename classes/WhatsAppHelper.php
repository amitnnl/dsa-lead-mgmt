<?php
/**
 * WhatsApp Helper - Generates pre-filled WhatsApp Web links for quick follow-ups
 */
class WhatsAppHelper {
    
    public static function getLink(string $phone, string $message): string {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10) $phone = '91' . $phone; // Default to India country code
        return "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($message);
    }

    public static function getTemplate(string $type, array $lead): string {
        $name = $lead['customer_name'];
        $loan = $lead['loan_type'];
        $amount = number_format($lead['loan_amount']);

        switch ($type) {
            case 'welcome':
                return "Hi {$name}, thank you for choosing our DSA services for your {$loan}. We have received your application for ₹{$amount}. Our representative will contact you soon.";
            
            case 'docs_pending':
                return "Dear {$name}, your {$loan} application is currently on hold. Please upload your Aadhar, PAN, and last 3 months salary slips to our portal or send them here. - DSA Team";
            
            case 'approved':
                return "Great news {$name}! Your {$loan} of ₹{$amount} has been APPROVED. Please share your bank details for disbursement process.";
            
            case 'disbursed':
                return "Congratulations {$name}! Your {$loan} of ₹{$amount} has been successfully disbursed. Thank you for your trust!";
            
            case 'followup':
                return "Hi {$name}, I tried calling you regarding your {$loan} application but couldn't connect. Please let me know a good time to talk.";
            
            default:
                return "Hi {$name}, regarding your {$loan} application...";
        }
    }
}
