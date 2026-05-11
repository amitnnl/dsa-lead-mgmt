<?php
/**
 * Lead Scoring Engine
 * Automatically calculates a lead score (0-100) based on data completeness and quality
 */
class LeadScorer {

    /**
     * Calculate score for a lead
     */
    public static function calculate(array $lead): int {
        $score = 0;

        // Contact info completeness (30 points max)
        if (!empty($lead['customer_name'])) $score += 10;
        if (!empty($lead['phone_number'])) $score += 10;
        if (!empty($lead['email_address'])) $score += 5;
        if (!empty($lead['city'])) $score += 5;

        // Financial data (30 points max)
        if (!empty($lead['loan_amount']) && $lead['loan_amount'] > 0) {
            $score += 15;
            // Higher loan amounts = higher score
            if ($lead['loan_amount'] >= 500000) $score += 5;
            if ($lead['loan_amount'] >= 1000000) $score += 5;
            if ($lead['loan_amount'] >= 2500000) $score += 5;
        }

        // Lead source quality (15 points max)
        $highQualitySources = ['Referral', 'Walk-in', 'Partner'];
        $mediumQualitySources = ['Website', 'Phone Inquiry'];
        if (in_array($lead['lead_source'] ?? '', $highQualitySources)) {
            $score += 15;
        } elseif (in_array($lead['lead_source'] ?? '', $mediumQualitySources)) {
            $score += 10;
        } else {
            $score += 5;
        }

        // Loan type specified (10 points)
        if (!empty($lead['loan_type'])) $score += 10;

        // Employer / income data (15 points max)
        if (!empty($lead['employer'])) $score += 8;
        if (!empty($lead['monthly_income']) && $lead['monthly_income'] > 0) $score += 7;

        return min(100, $score);
    }

    /**
     * Get the label for a score
     */
    public static function getLabel(int $score): string {
        foreach (LEAD_SCORES as $label => $config) {
            if ($score >= $config['min']) {
                return $label;
            }
        }
        return 'Cold';
    }

    /**
     * Get the color for a score
     */
    public static function getColor(int $score): string {
        foreach (LEAD_SCORES as $label => $config) {
            if ($score >= $config['min']) {
                return $config['color'];
            }
        }
        return '#6b7280';
    }
}
