<?php
/**
 * DSA LeadFlow - Git Synchronization Control Center
 * A powerful, beautiful web interface to run Git sync commands.
 */

session_start();

// Security check: Simple token to prevent unauthorized access from outside localhost
if (!isset($_SESSION['git_sync_token'])) {
    $_SESSION['git_sync_token'] = bin2hex(random_bytes(16));
}

$token = $_SESSION['git_sync_token'];
$output = '';
$action_taken = '';

// Check if request is from localhost
$is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost']) || isset($_GET['bypass']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || $_POST['token'] !== $token) {
        $output = "Security Error: Invalid token.";
    } elseif (!$is_local) {
        $output = "Security Error: Access restricted to localhost.";
    } else {
        $cmd_type = $_POST['action'] ?? '';
        $repoPath = __DIR__;
        
        switch ($cmd_type) {
            case 'status':
                $action_taken = 'Git Status';
                $command = 'git status 2>&1';
                break;
            case 'add':
                $action_taken = 'Stage Changes (git add -A)';
                $command = 'git add -A 2>&1';
                break;
            case 'commit':
                $msg = trim($_POST['commit_msg'] ?? 'Syncing changes via Git Control Center');
                $msg = empty($msg) ? 'Syncing changes via Git Control Center' : $msg;
                $action_taken = 'Commit Changes';
                $command = 'git commit -m ' . escapeshellarg($msg) . ' 2>&1';
                break;
            case 'pull':
                $action_taken = 'Pull from GitHub';
                $command = 'git pull origin main 2>&1';
                break;
            case 'push':
                $action_taken = 'Push to GitHub';
                $command = 'git push origin main 2>&1';
                break;
            case 'log':
                $action_taken = 'View Git History';
                $command = 'git log -n 5 --oneline --graph --decorate 2>&1';
                break;
            case 'diff':
                $action_taken = 'View Local Diff';
                $command = 'git diff 2>&1';
                break;
            default:
                $command = '';
        }
        
        if (!empty($command)) {
            $output_lines = [];
            $exit_code = 0;
            exec("cd " . escapeshellarg($repoPath) . " && " . $command, $output_lines, $exit_code);
            $output = "Running Command: " . htmlspecialchars($command) . "\n";
            $output .= "Exit Code: " . $exit_code . "\n\n";
            $output .= htmlspecialchars(implode("\n", $output_lines));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Git Synchronization Control Center | DSA LeadFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(20, 26, 46, 0.45);
            --border-color: rgba(255, 255, 255, 0.08);
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.15);
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.15);
            --warning: #f59e0b;
            --warning-glow: rgba(245, 158, 11, 0.15);
            --danger: #ef4444;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 10% 20%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--success));
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
        }

        .title-area h1 {
            font-size: 1.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, #fff 0%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .title-area h1 i {
            color: var(--primary);
            filter: drop-shadow(0 0 8px var(--primary));
        }

        .title-area p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .badge-status {
            background: var(--primary-glow);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #818cf8;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .control-panel {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        button {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.9rem 1.25rem;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        button:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        button i.arrow {
            transition: transform 0.3s ease;
            opacity: 0.5;
        }

        button:hover i.arrow {
            transform: translateX(4px);
            opacity: 1;
        }

        button.btn-primary {
            background: var(--primary);
            border-color: transparent;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        button.btn-primary:hover {
            background: #4f46e5;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
        }

        button.btn-success {
            background: var(--success);
            border-color: transparent;
            box-shadow: 0 4px 12px var(--success-glow);
        }

        button.btn-success:hover {
            background: #059669;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        button.btn-warning {
            background: var(--warning);
            border-color: transparent;
            box-shadow: 0 4px 12px var(--warning-glow);
        }

        button.btn-warning:hover {
            background: #d97706;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
        }

        .commit-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .input-group label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        input[type="text"] {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .output-panel {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 380px;
        }

        .terminal-header {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .terminal-dots {
            display: flex;
            gap: 6px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #10b981; }

        .terminal-title {
            font-family: 'Fira Code', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .console {
            background: #05070c;
            border: 1px solid var(--border-color);
            border-radius: 0 0 12px 12px;
            padding: 1.25rem;
            font-family: 'Fira Code', monospace;
            font-size: 0.85rem;
            color: #38bdf8;
            flex-grow: 1;
            overflow-y: auto;
            white-space: pre-wrap;
            box-shadow: inset 0 4px 20px rgba(0,0,0,0.8);
            line-height: 1.5;
            max-height: 450px;
        }

        .placeholder-text {
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 12px;
            opacity: 0.7;
        }

        .placeholder-text i {
            font-size: 2.5rem;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title-area">
                <h1><i class="fa-brands fa-github"></i> Git Sync Control Center</h1>
                <p>DSA LeadFlow Repository Management Platform</p>
            </div>
            <div class="badge-status">
                <i class="fas fa-circle-nodes"></i> Branch: main
            </div>
        </div>

        <div class="grid">
            <!-- Left Side: Controls -->
            <div class="control-panel">
                <div>
                    <h3 class="section-title"><i class="fas fa-search-dollar"></i> Phase 1: Inspection & Prep</h3>
                    <div class="btn-group">
                        <form method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="status">
                            <button type="submit">
                                <span><i class="fas fa-info-circle" style="color: var(--primary); margin-right: 8px;"></i> Git Status</span>
                                <i class="fas fa-chevron-right arrow"></i>
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="diff">
                            <button type="submit">
                                <span><i class="fas fa-file-signature" style="color: var(--warning); margin-right: 8px;"></i> View Local Diff</span>
                                <i class="fas fa-chevron-right arrow"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div>
                    <h3 class="section-title"><i class="fas fa-tasks"></i> Phase 2: Stage & Commit</h3>
                    <div class="commit-box">
                        <form method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" style="margin-bottom: 0.75rem;">
                                <span><i class="fas fa-plus" style="color: var(--success); margin-right: 8px;"></i> Stage All Local Changes</span>
                                <i class="fas fa-chevron-right arrow"></i>
                            </button>
                        </form>

                        <form method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="commit">
                            <div class="input-group">
                                <label for="commit_msg">Commit Message</label>
                                <input type="text" id="commit_msg" name="commit_msg" placeholder="e.g. Syncing changes and bug fixes">
                            </div>
                            <button type="submit" class="btn-primary" style="margin-top: 0.75rem;">
                                <span><i class="fas fa-check" style="margin-right: 8px;"></i> Commit Staged Changes</span>
                                <i class="fas fa-arrow-right arrow"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div>
                    <h3 class="section-title"><i class="fas fa-cloud-arrow-up"></i> Phase 3: Push & Pull (Sync)</h3>
                    <div class="btn-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <form method="POST" style="grid-column: span 1;">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="pull">
                            <button type="submit" class="btn-warning">
                                <span><i class="fas fa-arrow-down"></i> Pull</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </form>
                        <form method="POST" style="grid-column: span 1;">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="push">
                            <button type="submit" class="btn-success">
                                <span><i class="fas fa-arrow-up"></i> Push</span>
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="action" value="log">
                        <button type="submit">
                            <span><i class="fas fa-history" style="color: var(--text-muted); margin-right: 8px;"></i> View Recent History</span>
                            <i class="fas fa-chevron-right arrow"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Side: Terminal Output -->
            <div class="output-panel">
                <div class="terminal-header">
                    <div class="terminal-dots">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                    </div>
                    <div class="terminal-title">bash - dsa-lead-mgmt</div>
                </div>
                <div class="console">
                    <?php if (!empty($output)): ?>
                        <?php echo $output; ?>
                    <?php else: ?>
                        <div class="placeholder-text">
                            <i class="fa-brands fa-github-alt"></i>
                            <p>Ready to sync with GitHub.</p>
                            <p style="font-size: 0.75rem; text-align: center;">Click any of the action buttons on the left to execute Git commands.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
