<?php
/**
 * Commission Engine - Calculates Agent and Manager payouts
 */
class CommissionEngine {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Calculate potential commission for a lead
     */
    public function calculateForLead(array $lead): array {
        $amount = $lead['loan_amount'] ?? 0;
        $bank = $lead['bank_name'] ?? '';
        $loanType = $lead['loan_type'] ?? '';
        $agentId = $lead['assigned_to'] ?? null;

        if (!$amount || !$bank || !$loanType) return ['total' => 0, 'agent' => 0, 'company' => 0];

        // 1. Get Base Bank Commission
        $rate = $this->db->fetch(
            "SELECT commission_percentage FROM commission_rates WHERE bank_name = ? AND loan_type = ?",
            [$bank, $loanType]
        );
        $baseRate = $rate ? (float)$rate['commission_percentage'] : 0;
        $totalCommission = ($amount * $baseRate) / 100;

        // 2. Get Agent Share (Based on Slabs or fixed default)
        // For simplicity, we fetch the active slab for the agent's monthly performance
        // In a real system, we'd sum up their disbursements for the current month
        $agentSharePct = 50; // Default: 50% of bank commission goes to agent
        
        $slab = $this->db->fetch(
            "SELECT agent_share_percentage FROM payout_slabs WHERE ? BETWEEN min_volume AND max_volume LIMIT 1",
            [$amount] // In production, this would be total_monthly_volume
        );
        if ($slab) $agentSharePct = (float)$slab['agent_share_percentage'];

        $agentPayout = ($totalCommission * $agentSharePct) / 100;
        
        // 3. Get Manager Override (if applicable)
        $managerPayout = 0;
        $managerId = null;
        if ($agentId) {
            $agent = $this->db->fetch("SELECT parent_id FROM users WHERE id = ?", [$agentId]);
            if ($agent && $agent['parent_id']) {
                $managerId = $agent['parent_id'];
                // Default override: 5% of the TOTAL bank commission
                $managerPayout = ($totalCommission * 5) / 100;
            }
        }

        $companyShare = $totalCommission - $agentPayout - $managerPayout;

        return [
            'total_commission' => $totalCommission,
            'agent_payout' => $agentPayout,
            'manager_payout' => $managerPayout,
            'manager_id' => $managerId,
            'company_share' => $companyShare,
            'bank_rate' => $baseRate,
            'agent_rate' => $agentSharePct
        ];
    }
}
