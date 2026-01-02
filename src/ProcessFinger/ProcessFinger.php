<?php namespace Nabeghe\ProcessFinger;


/**
 * ProcessFinger: cross-platform utilities for inspecting and managing system processes.
 * Works on 🐧 Linux, 🍎 Mac, BSD, and 🪟 Windows.. Some methods need elevated privileges
 * or enabled shell_exec/exec. Unix-only: environment variables.
 */
final class ProcessFinger
{
    #region Basic Info

    /**
     * Get the current process ID.
     *
     * @return int
     */
    public static function getId(): int
    {
        return getmypid();
    }

    /**
     * Get the parent process ID (PPID) of a given process.
     *
     * @param  int|null  $pid  Process ID. Defaults to current process.
     * @return int|null Parent process ID, or null if unavailable.
     */
    public static function getParentId(?int $pid = null): ?int
    {
        $pid = $pid ?? getmypid();
        if ($pid <= 0) {
            return null;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec("wmic process where ProcessId=$pid get ParentProcessId 2>NUL");
            if ($output && preg_match('/\d+/', $output, $matches)) {
                return (int) $matches[0];
            }
        } else {
            $ppid = (int) trim(shell_exec("ps -p $pid -o ppid="));
            return $ppid > 0 ? $ppid : null;
        }

        return null;
    }

    /**
     * Get the absolute path to the script associated with a given process ID.
     *
     * Supports 🐧 Linux, 🍎 Mac, BSD, and 🪟 Windows.
     *
     * @param  int|null  $pid  Optional process ID. Defaults to the current process if null.
     * @return string|null Absolute path to the script, or null if unavailable.
     */
    public static function getScriptPath(?int $pid = null): ?string
    {
        if ($pid === null) {
            $pid = getmypid();
            if ($pid === false) {
                return null;
            }
        }

        if (
            !function_exists('shell_exec') ||
            (ini_get('disable_functions') && strpos(ini_get('disable_functions'), 'shell_exec') !== false)
        ) {
            return null;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'LIN') {
            $cmdlinePath = "/proc/$pid/cmdline";
            if (!is_readable($cmdlinePath)) {
                return null;
            }

            $args = array_values(array_filter(explode("\0", file_get_contents($cmdlinePath))));

            foreach ($args as $arg) {
                if ($arg[0] === '-') {
                    continue;
                }

                if (substr($arg, -4) !== '.php') {
                    continue;
                }

                if (is_file($arg)) {
                    return realpath($arg);
                }

                $cwdPath = "/proc/$pid/cwd";
                if (is_link($cwdPath)) {
                    $cwd = readlink($cwdPath);
                    if ($cwd && is_file($cwd . '/' . $arg)) {
                        return realpath($cwd . '/' . $arg);
                    }
                }
            }

            return null;
        }

        if ($os === 'WIN') {
            $command = "powershell -NoProfile -Command \"(Get-CimInstance Win32_Process -Filter \\\"ProcessId='$pid'\\\").CommandLine\" 2>&1";
            $output = trim((string) shell_exec($command));

            if ($output === '') {
                return null;
            }

            preg_match_all('/"([^"]+)"|(\S+)/', $output, $matches);
            $args = array_values(array_filter(array_merge($matches[1], $matches[2])));

            foreach ($args as $arg) {
                if ($arg[0] === '-') {
                    continue;
                }

                if (substr($arg, -4) !== '.php') {
                    continue;
                }

                if (is_file($arg)) {
                    return realpath($arg);
                }
            }

            return null;
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            $commandLine = trim((string) shell_exec("ps -p $pid -o command= 2>/dev/null"));
            if ($commandLine === '') {
                return null;
            }

            preg_match_all('/"([^"]+)"|\'([^\']+)\'|(\S+)/', $commandLine, $matches);
            $args = array_values(array_filter(array_merge($matches[1], $matches[2], $matches[3])));

            foreach ($args as $arg) {
                if ($arg[0] === '-') {
                    continue;
                }

                if (substr($arg, -4) !== '.php') {
                    continue;
                }

                if (is_file($arg)) {
                    return realpath($arg);
                }
            }

            return null;
        }

        return null;
    }

    /**
     * Get the name of a process (or the main command) for a given PID.
     *
     * Supports 🐧 Linux, 🍎 Mac, BSD, and 🪟 Windows.
     *
     * @param  int|null  $pid  Process ID. Defaults to current process.
     * @return string|null Process name or command, or null if unavailable.
     */
    public static function getProcessName(?int $pid = null): ?string
    {
        $pid = $pid ?? getmypid();
        if ($pid <= 0) {
            return null;
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            $output = shell_exec("ps -p $pid -o comm=");
            if ($output !== null) {
                return trim($output);
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec("wmic process where ProcessId=$pid get Name 2>NUL");
            if ($output) {
                $lines = explode(PHP_EOL, $output);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line !== '' && strtolower($line) !== 'name') {
                        return $line;
                    }
                }
            }
        }

        return null;
    }

    #endregion

    #region State Checkers

    /**
     * Check if a process with the given PID exists.
     *
     * Supports 🐧 Linux, 🍎 Mac, BSD, and 🪟 Windows.
     *
     * @param  int  $pid  The process ID to check.
     * @return bool|null Returns true if the process exists, false if it does not, or null if undetermined.
     */
    public static function exists(int $pid): ?bool
    {
        if ($pid <= 0) {
            return false;
        }

        try {
            if (function_exists('posix_kill')) {
                return @posix_kill($pid, 0);
            }

            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = "tasklist /FI \"PID eq $pid\" 2>NUL";
                exec($cmd, $output, $code);

                if ($code !== 0 || empty($output)) {
                    return false;
                }

                foreach ($output as $line) {
                    if (preg_match('/\b'.preg_quote((string) $pid, '/').'\b/', $line)) {
                        return true;
                    }
                }

                return false;
            }
        } catch (\Throwable $exception) {
        }

        return null;
    }

    /**
     * Check if the current process is running with elevated privileges (root/admin).
     *
     * @return bool True if running as root/admin, false otherwise.
     */
    public static function isRunningAsRoot(): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return function_exists('posix_geteuid') && posix_geteuid() === 0;
        }

        $output = shell_exec('net session 2>&1');
        return $output !== false && stripos($output, 'Access is denied') === false;
    }

    /**
     * Check if a process is in a zombie state.
     *
     * Only supported on Unix-like systems (Linux/Mac/BSD). Returns false on Windows.
     *
     * @param  int  $pid  Process ID to check.
     * @return bool True if the process is a zombie, false otherwise.
     */
    public static function isZombie(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return false;
        }

        $state = trim(shell_exec("ps -p $pid -o stat="));
        if ($state === '') {
            return false;
        }

        return strpos($state, 'Z') !== false;
    }

    #endregion

    #region Resource Info

    /**
     * Get memory usage (in bytes) of a process.
     *
     * @param  int|null  $pid  Process ID. Defaults to current process.
     * @return int|null Memory usage in bytes, or null if unavailable.
     */
    public static function getMemoryUsage(?int $pid = null): ?int
    {
        $pid = $pid ?? getmypid();
        if ($pid <= 0) {
            return null;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec("wmic process where ProcessId=$pid get WorkingSetSize 2>NUL");
            if ($output && preg_match('/\d+/', $output, $matches)) {
                return (int) $matches[0];
            }
        } else {
            $output = shell_exec("ps -p $pid -o rss=");
            if ($output) {
                return (int) trim($output) * 1024;
            }
        }

        return null;
    }

    /**
     * Get the CPU usage percentage of a process.
     *
     * Supports 🐧 Linux, 🍎 Mac, BSD, and 🪟 Windows. (limited precision).
     *
     * @param  int|null  $pid  Process ID. Defaults to current process.
     * @return float|null CPU usage in percent, or null if unavailable.
     */
    public static function getCpuUsage(?int $pid = null): ?float
    {
        $pid = $pid ?? getmypid();
        if ($pid <= 0) {
            return null;
        }

        if (PHP_OS_FAMILY !== 'Windows' && PHP_OS_FAMILY !== 'Darwin') {
            $output = shell_exec("ps -p $pid -o %cpu=");
            if ($output !== null) {
                return (float) trim($output);
            }
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            $output = shell_exec("ps -p $pid -o %cpu=");
            if ($output !== null) {
                return (float) trim($output);
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec("wmic path Win32_PerfFormattedData_PerfProc_Process where IDProcess=$pid get PercentProcessorTime 2>NUL");
            if ($output) {
                $lines = explode(PHP_EOL, $output);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (is_numeric($line)) {
                        return (float) $line;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get environment variables of a process.
     *
     * Only supported on Unix-like systems (Linux/Mac/BSD).
     * Returns null on Windows or if unavailable.
     *
     * @param  int|null  $pid  Process ID. Defaults to current process.
     * @return array<string,string>|null Associative array of environment variables, or null if unavailable.
     */
    public static function getProcessEnv(?int $pid = null): ?array
    {
        $pid = $pid ?? getmypid();
        if ($pid <= 0) {
            return null;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return null;
        }

        $envFile = "/proc/$pid/environ";
        if (!file_exists($envFile)) {
            return null;
        }

        $content = file_get_contents($envFile);
        if ($content === false) {
            return null;
        }

        $vars = [];
        foreach (explode("\0", $content) as $pair) {
            if ($pair === '') {
                continue;
            }
            [$key, $value] = explode('=', $pair, 2) + [null, null];
            if ($key !== null) {
                $vars[$key] = $value;
            }
        }

        return $vars;
    }

    #endregion

    #region Actions / Operations

    /**
     * Get the list of child process IDs (PPID) for a given process.
     *
     * Supports 🐧 Linux, 🍎 Mac, BSD, and 🪟 Windows..
     *
     * @param  int|null  $pid  Process ID to check. Defaults to current process.
     * @return int[] Array of child PIDs. Empty array if none found or unsupported.
     */
    public static function listChildren(?int $pid = null): array
    {
        $pid = $pid ?? getmypid();
        if ($pid <= 0) {
            return [];
        }

        $children = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec("wmic process where ParentProcessId=$pid get ProcessId 2>NUL");
        } else {
            $output = shell_exec("ps -o pid= --ppid $pid");
        }

        if ($output) {
            foreach (explode(PHP_EOL, $output) as $line) {
                $line = trim($line);
                if (ctype_digit($line)) {
                    $children[] = (int) $line;
                }
            }
        }

        return $children;
    }

    /**
     * Terminate a process with the given PID.
     *
     * Uses SIGKILL or SIGTERM on Linux/Mac/BSD, and taskkill on Windows.
     *
     * @param  int  $pid  The process ID to terminate.
     * @param  bool  $force  Whether to forcefully terminate the process (default true).
     * @return bool|null Returns true on success, false on failure, or null if operation cannot be performed.
     */
    public static function kill(int $pid, bool $force = true): ?bool
    {
        if ($pid <= 0) {
            return false;
        }

        if (function_exists('posix_kill')) {

            $signal = $force ? SIGKILL : SIGTERM;

            if (!@posix_kill($pid, 0)) {
                return false;
            }

            return @posix_kill($pid, $signal);
        }

        if (PHP_OS_FAMILY === 'Windows') {

            $cmd = $force
                ? "taskkill /PID $pid /F"
                : "taskkill /PID $pid";

            exec($cmd, $output, $code);

            return $code === 0;
        }

        return false;
    }

    /**
     * Wait until a given process exits.
     *
     * @param  int  $pid  Process ID to wait for.
     * @param  int  $timeout  Optional timeout in seconds. 0 = infinite. Default 0.
     * @return bool True if the process exited, false if timeout reached or PID invalid.
     */
    public static function wait(int $pid, int $timeout = 0): bool
    {
        if ($pid <= 0) {
            return false;
        }

        $start = time();

        while (true) {
            $exists = self::exists($pid);
            if (!$exists) {
                return true;
            }

            if ($timeout > 0 && (time() - $start) >= $timeout) {
                return false;
            }

            usleep(100_000);
        }
    }

    /**
     * Restart a process by killing it and executing the given command.
     *
     * Note: This does not preserve the original environment or arguments automatically.
     *
     * @param  int  $pid  Process ID to restart.
     * @param  string  $command  Command to start the process again.
     * @param  bool  $force  Whether to force kill the process (default true).
     * @return int|null New process ID if started successfully, null on failure.
     */
    public static function restart(int $pid, string $command, bool $force = true): ?int
    {
        if ($pid <= 0 || $command === '') {
            return null;
        }

        $killed = self::kill($pid, $force);
        if (!$killed) {
            return null;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /b $command", "r"));
        } else {
            exec("$command > /dev/null 2>&1 &");
        }

        usleep(100_000);

        $children = self::listChildren(getmypid());
        return $children[0] ?? null;
    }

    #endregion
}
