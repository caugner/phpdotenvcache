<?php

use Dotenv\CachedDotenv;

class CachedDotenvTest extends \PHPUnit\Framework\TestCase {

  public function testConstructor() {
    $dotenv = new CachedDotenv(__DIR__);
    $this->assertNotNull($dotenv);
  }

}
