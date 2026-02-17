<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Implementation;

use JR\Tracker\Config;
use JR\Tracker\Service\Contract\LoggerServiceInterface;

class LoggerService implements LoggerServiceInterface
{
  private string $logFile;

  public function __construct(private readonly Config $config)
  {
    $this->logFile = STORAGE_PATH . '/logs/app.log';
    $this->ensureLogDirectoryExists();
  }

  public function error(string $message, array $context = []): void
  {
    $this->log('ERROR', $message, $context);
  }

  public function warning(string $message, array $context = []): void
  {
    $this->log('WARNING', $message, $context);
  }

  public function info(string $message, array $context = []): void
  {
    $this->log('INFO', $message, $context);
  }

  public function debug(string $message, array $context = []): void
  {
    // Logovat debug pouze v development
    if ($this->config->get('display_error_details')) {
      $this->log('DEBUG', $message, $context);
    }
  }

  private function log(string $level, string $message, array $context): void
  {
    $timestamp = date('Y-m-d H:i:s');
    $contextJson = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $logMessage = "[{$timestamp}] {$level}: {$message}{$contextJson}" . PHP_EOL;

    // Zapsat do souboru
    file_put_contents($this->logFile, $logMessage, FILE_APPEND);

    // Také zapsat do PHP error logu pro kritické chyby
    if ($level === 'ERROR') {
      error_log($message);
    }
  }

  private function ensureLogDirectoryExists(): void
  {
    $logDir = dirname($this->logFile);
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
  }
}
