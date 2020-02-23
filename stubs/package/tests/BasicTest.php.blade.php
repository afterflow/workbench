@startPhp

namespace {{$vendorTitle}}\{{$packageTitle}}\Tests;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase {

    public function testBasicApi(  ) {
        $this->assertEquals(1,1);
    }

}
