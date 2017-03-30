<?php
namespace Dotenv;

class CachedDotenv extends Dotenv {

  const DEFAULT_ENV_FILE = '.env';
  const DEFAULT_CACHE_FILE = '.env.cache.php';

  const TO_ENV = 1;
  const TO_SERVER = 2;
  const TO_ENV_AND_SERVER = self::TO_ENV | self::TO_SERVER;

  protected $envFilePath;
  protected $cacheFilePath;

  public function __construct($path, $envFile = null, $cacheFile = null) {
    parent::__construct($path);
    $this->envFilePath = $this->getRealFilePath($path, $envFile, self::DEFAULT_ENV_FILE);
    $this->cacheFilePath = $this->getRealFilePath($path, $cacheFile, self::DEFAULT_CACHE_FILE);
  }

  protected function getRealFilePath($path, $file, $defaultFile)
  {
      if (!is_string($file)) {
          $file = $defaultFile;
      }
      $filePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
      return $filePath;
  }

  public function load() {
    $this->loadWithOptions(self::TO_ENV_AND_SERVER);
  }

  public function loadToEnv() {
    $this->loadWithOptions(self::TO_ENV);
  }

  public function loadToServer() {
    $this->loadWithOptions(self::TO_SERVER);
  }

  protected function loadWithOptions($options) {
    $env = $this->loadAndCache();
    array_walk($env, function($value, $key) use ($options) {
      if ($options & self::TO_ENV) {
        $_ENV[$key]    = $value;
      }
      if ($options & self::TO_SERVER) {
        $_SERVER[$key] = $value;
      }
    });
    return $env;
  }

  protected function loadAndCache() {
    if ($this->isCacheOutdated()) {
      $this->renewCache($this->cacheFilePath);
    }

    return $this->readCache($this->cacheFilePath);
  }

  private function renewCache($cacheFilePath) {
    $env = $this->loadFromEnv();
    $this->writeCache($env, $cacheFilePath);
  }

  private function loadFromEnv() {
    $oldKeys = array_keys($_ENV);

    parent::load();

    $currentKeys = array_keys($_ENV);
    $newKeys = array_diff($currentKeys, $oldKeys);

    $env = [];
    array_map(function($key) use (&$env) {
      $env[$key] = $_ENV[$key];
      unset($_ENV[$key]);
      unset($_SERVER[$key]);
    }, $newKeys);

    return $env;
  }

  private function isCacheOutdated() {
    return !file_exists($this->cacheFilePath) ||
           filemtime($this->cacheFilePath) < filemtime($this->envFilePath);
  }

  private function writeCache($env, $cacheFilePath) {
    $cacheContent = '<?php return unserialize(\'' . serialize($env) . '\');';
    file_put_contents($cacheFilePath, $cacheContent, LOCK_EX);
  }

  private function readCache($cacheFilePath) {
    return $this->includeWithSharedLock($cacheFilePath);
  }

  private function includeWithSharedLock($file) {
    $fp = fopen($file, "r");
    flock($fp, LOCK_SH);
    $result = include($file);
    flock($fp, LOCK_UN);
    return $result;
  }
}
