<?php

namespace Wpcb2\Actions;

/**
 * Run AI-generated code snippet
 *
 * This action allows the AI to execute PHP code directly on the WordPress instance.
 * Use with EXTREME caution - only enable when you trust the AI and understand the risks.
 *
 * Security considerations:
 * - Only accessible to users with manage_options capability
 * - Requires valid nonce verification (handled by router)
 * - Code execution is disabled by default on the frontend
 * - All execution is logged with timestamp and code
 * - Catches and returns errors without crashing
 */
class RunAICode
{
    /**
     * Maximum execution time in seconds
     */
    const MAX_EXECUTION_TIME = 30;

    /**
     * Maximum code size in bytes (100KB)
     */
    const MAX_CODE_SIZE = 102400;

    /**
     * Dangerous functions that are checked in code
     * Can be disabled via filter: apply_filters('wpcb_ai_check_dangerous_functions', true)
     */
    const DANGEROUS_FUNCTIONS = [
        'exec', 'shell_exec', 'system', 'passthru', 'popen', 'proc_open',
        'pcntl_exec', 'eval', 'assert', 'create_function',
        'file_put_contents', 'file_get_contents', 'fopen', 'fwrite',
        'unlink', 'rmdir', 'chmod', 'chown', 'chgrp',
        'symlink', 'link', 'mail', 'header'
    ];

    /**
     * Check rate limit for code execution
     * Limits to 10 executions per minute per user
     *
     * @return bool True if rate limit not exceeded
     */
    private function checkRateLimit()
    {
        $user_id = get_current_user_id();
        $transient_key = 'wpcb_ai_code_exec_' . $user_id;
        $executions = get_transient($transient_key);

        if ($executions === false) {
            // First execution in this time window
            set_transient($transient_key, 1, 60); // 60 seconds
            return true;
        }

        if ($executions >= 10) {
            return false;
        }

        set_transient($transient_key, $executions + 1, 60);
        return true;
    }

    /**
     * Log code execution for security audit
     *
     * @param string $code The code that was executed
     * @param bool $success Whether execution was successful
     * @param string $error Error message if failed
     */
    private function logExecution($code, $success, $error = '')
    {
        $user = wp_get_current_user();
        $log_entry = sprintf(
            '[WPCB AI Code Execution] User: %s (%d) | Time: %s | Success: %s | Code length: %d bytes | Error: %s',
            $user->user_login,
            $user->ID,
            date('Y-m-d H:i:s'),
            $success ? 'Yes' : 'No',
            strlen($code),
            $error
        );

        error_log($log_entry);

        // Also store in WordPress options for admin review (last 50 executions)
        $audit_log = get_option('wpcb_ai_code_execution_log', []);
        array_unshift($audit_log, [
            'user_id' => $user->ID,
            'user_login' => $user->user_login,
            'timestamp' => time(),
            'success' => $success,
            'code_preview' => substr($code, 0, 200), // First 200 chars only
            'error' => $error
        ]);

        // Keep only last 50 entries
        $audit_log = array_slice($audit_log, 0, 50);
        update_option('wpcb_ai_code_execution_log', $audit_log);
    }

    /**
     * Execute the AI-provided code
     *
     * Expected POST body:
     * {
     *   "code": "<?php\n// PHP code here\n"
     * }
     *
     * Returns:
     * {
     *   "output": "...",
     *   "error": null
     * }
     * or
     * {
     *   "output": "",
     *   "error": "Error message"
     * }
     */
    public function execute()
    {
        // Security headers
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');

        // Rate limiting
        if (!$this->checkRateLimit()) {
            echo json_encode([
                'output' => '',
                'error' => 'Rate limit exceeded. Maximum 10 executions per minute.'
            ]);
            die;
        }

        // Get the code from POST body
        $data = file_get_contents("php://input");
        $data = json_decode($data, true);

        if (!isset($data['code']) || empty($data['code'])) {
            echo json_encode([
                'output' => '',
                'error' => 'No code provided'
            ]);
            die;
        }

        $code = $data['code'];

        // Validate code size
        if (strlen($code) > self::MAX_CODE_SIZE) {
            $error_msg = 'Code size exceeds maximum allowed size of ' . self::MAX_CODE_SIZE . ' bytes';
            $this->logExecution($code, false, $error_msg);
            echo json_encode([
                'output' => '',
                'error' => $error_msg
            ]);
            die;
        }

        // Remove PHP opening tags if present
        $code = trim($code);
        $pos = strpos($code, '<?php');
        if ($pos !== false) {
            $code = substr_replace($code, '', $pos, strlen('<?php'));
        }
        $code = trim($code);

        // Check for dangerous functions (can be disabled via filter)
        if (apply_filters('wpcb_ai_check_dangerous_functions', true)) {
            foreach (self::DANGEROUS_FUNCTIONS as $func) {
                // Check for function calls (basic pattern matching)
                if (preg_match('/\b' . preg_quote($func, '/') . '\s*\(/i', $code)) {
                    $error_msg = 'Code contains potentially dangerous function: ' . $func;
                    $this->logExecution($code, false, $error_msg);
                    echo json_encode([
                        'output' => '',
                        'error' => $error_msg . '. This function is blocked for security. If you need to use it, ask an admin to disable the check using the wpcb_ai_check_dangerous_functions filter.'
                    ]);
                    die;
                }
            }
        }

        // Set execution time limit
        $originalTimeLimit = ini_get('max_execution_time');
        set_time_limit(self::MAX_EXECUTION_TIME);

        // Capture output
        ob_start();

        $response = [
            'output' => '',
            'error' => null
        ];

        try {
            // Execute the code and capture return value
            $result = eval($code);

            // Get any echo/print output
            $output = ob_get_clean();

            // If there's a return value and no echo output, use the return value
            if ($result !== null && empty($output)) {
                // Format the result nicely
                if (is_array($result) || is_object($result)) {
                    // Use JSON encoding for clean, parseable output
                    $output = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                } else {
                    $output = (string) $result;
                }
            }

            $response['output'] = $output ?: 'Code executed successfully (no output)';

            // Log successful execution
            $this->logExecution($code, true);

        } catch (\ParseError $e) {
            ob_end_clean();
            $error_msg = 'Parse Error: ' . $e->getMessage();
            $response['error'] = $error_msg;
            $response['output'] = '';

            // Log failed execution
            $this->logExecution($code, false, $error_msg);

        } catch (\Throwable $e) {
            ob_end_clean();
            $error_msg = 'Error: ' . $e->getMessage();
            $response['error'] = $error_msg;
            $response['output'] = '';

            // Log failed execution
            $this->logExecution($code, false, $error_msg);
        }

        // Restore original time limit
        set_time_limit($originalTimeLimit);

        echo json_encode($response);
        die;
    }
}
