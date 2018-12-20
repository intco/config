<?php
namespace Noodlehaus;

use PHPUnit\Framework\TestCase;
use Noodlehaus\Test\Fixture\SimpleConfig;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-04-21 at 22:37:22.
 */
class AbstractConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->config = new SimpleConfig(
            [
                'host' => 'localhost',
                'port'    => 80,
                'servers' => [
                    'host1',
                    'host2',
                    'host3'
                ],
                'application' => [
                    'name'   => 'configuration',
                    'secret' => 's3cr3t',
                    'runtime' => null,
                ],
                'user' => null,
            ]
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Noodlehaus\AbstractConfig::__construct()
     * @covers Noodlehaus\AbstractConfig::getDefaults()
     */
    public function testDefaultOptionsSetOnInstantiation()
    {
        $config = new SimpleConfig(
            [
                'host' => 'localhost',
                'port'    => 80,
            ]
        );
        $this->assertEquals('localhost', $config->get('host'));
        $this->assertEquals(80, $config->get('port'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::get()
     */
    public function testGet()
    {
        $this->assertEquals('localhost', $this->config->get('host'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::get()
     */
    public function testGetWithDefaultValue()
    {
        $this->assertEquals(128, $this->config->get('ttl', 128));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::get()
     */
    public function testGetNestedKey()
    {
        $this->assertEquals('configuration', $this->config->get('application.name'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::get()
     */
    public function testGetNestedKeyWithDefaultValue()
    {
        $this->assertEquals(128, $this->config->get('application.ttl', 128));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::get()
     */
    public function testGetNonexistentKey()
    {
        $this->assertNull($this->config->get('proxy'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::get()
     */
    public function testGetNonexistentNestedKey()
    {
        $this->assertNull($this->config->get('proxy.name'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::get()
     */
    public function testGetReturnsArray()
    {
        $this->assertArrayHasKey('name', $this->config->get('application'));
        $this->assertEquals('configuration', $this->config->get('application.name'));
        $this->assertCount(3, $this->config->get('application'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::set()
     */
    public function testSet()
    {
        $this->config->set('region', 'apac');
        $this->assertEquals('apac', $this->config->get('region'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::set()
     */
    public function testSetNestedKey()
    {
        $this->config->set('location.country', 'Singapore');
        $this->assertEquals('Singapore', $this->config->get('location.country'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::set()
     */
    public function testSetArray()
    {
        $this->config->set('database', [
            'host' => 'localhost',
            'name' => 'mydatabase'
        ]);
        $this->assertTrue(is_array($this->config->get('database')));
        $this->assertEquals('localhost', $this->config->get('database.host'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::set()
     */
    public function testCacheWithNestedArray()
    {
        $this->config->set('database', [
            'host' => 'localhost',
            'name' => 'mydatabase'
        ]);
        $this->assertTrue(is_array($this->config->get('database')));
        $this->config->set('database.host', '127.0.0.1');
        $expected = [
            'host' => '127.0.0.1',
            'name' => 'mydatabase'
        ];
        $this->assertEquals($expected, $this->config->get('database'));

        $this->config->set('config', [
            'database' => [
                'host' => 'localhost',
                'name' => 'mydatabase'
            ]
        ]);
        $this->config->get('config'); //Just get to set related cache
        $this->config->get('config.database'); //Just get to set related cache

        $this->config->set('config.database.host', '127.0.0.1');
        $expected = [
            'database' => [
                'host' => '127.0.0.1',
                'name' => 'mydatabase'
            ]
        ];
        $this->assertEquals($expected, $this->config->get('config'));

        $expected = [
            'host' => '127.0.0.1',
            'name' => 'mydatabase'
        ];
        $this->assertEquals($expected, $this->config->get('config.database'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::set()
     */
    public function testCacheWithNestedMiddleArray()
    {
        $this->config->set('config', [
          'database' => [
              'host' => 'localhost',
              'name' => 'mydatabase'
          ]
        ]);
        $this->config->get('config'); //Just get to set related cache
        $this->config->get('config.database'); //Just get to set related cache
        $this->config->get('config.database.host'); //Just get to set related cache
        $this->config->get('config.database.name'); //Just get to set related cache

        $this->config->set('config.database', [
          'host' => '127.0.0.1',
          'name' => 'mynewdatabase'
        ]);
        $this->assertEquals('127.0.0.1', $this->config->get('config.database.host'));
        $this->assertEquals('mynewdatabase', $this->config->get('config.database.name'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::set()
     */
    public function testSetAndUnsetArray()
    {
        $this->config->set('database', [
            'host' => 'localhost',
            'name' => 'mydatabase'
        ]);
        $this->assertTrue(is_array($this->config->get('database')));
        $this->assertEquals('localhost', $this->config->get('database.host'));
        $this->config->set('database.host', null);
        $this->assertNull($this->config->get('database.host'));
        $this->config->set('database', null);
        $this->assertNull($this->config->get('database'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::has()
     */
    public function testHas()
    {
        $this->assertTrue($this->config->has('application'));
        $this->assertTrue($this->config->has('user'));
        $this->assertFalse($this->config->has('not_exist'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::has()
     */
    public function testHasNestedKey()
    {
        $this->assertTrue($this->config->has('application.name'));
        $this->assertTrue($this->config->has('application.runtime'));
        $this->assertFalse($this->config->has('application.not_exist'));
        $this->assertFalse($this->config->has('not_exist.name'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::has()
     */
    public function testHasCache()
    {
        $this->assertTrue($this->config->has('application.name'));
        $this->assertTrue($this->config->has('application.name'));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::all()
     */
    public function testAll()
    {
        $all = [
            'host' => 'localhost',
            'port'    => 80,
            'servers' => [
                'host1',
                'host2',
                'host3'
            ],
            'application' => [
                'name'   => 'configuration',
                'secret' => 's3cr3t',
                'runtime' => null,
            ],
            'user' => null,
        ];
        $this->assertEquals($all, $this->config->all());
    }

    /**
     * @covers Noodlehaus\AbstractConfig::merge()
     */
    public function testMerge()
    {
        $remote = new SimpleConfig(
            [
                'host' => '127.0.0.1'
            ]
        );

        $this->config->merge($remote);

        $this->assertEquals('127.0.0.1', $this->config['host']);
    }

    /**
     * @covers Noodlehaus\AbstractConfig::offsetGet()
     */
    public function testOffsetGet()
    {
        $this->assertEquals('localhost', $this->config['host']);
    }

    /**
     * @covers Noodlehaus\AbstractConfig::offsetGet()
     */
    public function testOffsetGetNestedKey()
    {
        $this->assertEquals('configuration', $this->config['application.name']);
    }

    /**
     * @covers Noodlehaus\AbstractConfig::offsetExists()
     */
    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->config['host']));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::offsetExists()
     */
    public function testOffsetExistsReturnsFalseOnNonexistentKey()
    {
        $this->assertFalse(isset($this->config['database']));
    }

    /**
     * @covers Noodlehaus\AbstractConfig::offsetSet()
     */
    public function testOffsetSet()
    {
        $this->config['newkey'] = 'newvalue';
        $this->assertEquals('newvalue', $this->config['newkey']);
    }

    /**
     * @covers Noodlehaus\AbstractConfig::offsetUnset()
     */
    public function testOffsetUnset()
    {
        unset($this->config['application']);
        $this->assertNull($this->config['application']);
    }

    /**
     * @covers Noodlehaus\AbstractConfig::current()
     */
    public function testCurrent()
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();
        $this->assertEquals($this->config['host'], $this->config->current());

        /* Step through each of the other elements of the test config */
        $this->config->next();
        $this->assertEquals($this->config['port'], $this->config->current());
        $this->config->next();
        $this->assertEquals($this->config['servers'], $this->config->current());
        $this->config->next();
        $this->assertEquals($this->config['application'], $this->config->current());
        $this->config->next();
        $this->assertEquals($this->config['user'], $this->config->current());

        /* Step beyond the end and confirm the result */
        $this->config->next();
        $this->assertFalse($this->config->current());
    }

    /**
     * @covers Noodlehaus\AbstractConfig::key()
     */
    public function testKey()
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();
        $this->assertEquals('host', $this->config->key());

        /* Step through each of the other elements of the test config */
        $this->config->next();
        $this->assertEquals('port', $this->config->key());
        $this->config->next();
        $this->assertEquals('servers', $this->config->key());
        $this->config->next();
        $this->assertEquals('application', $this->config->key());
        $this->config->next();
        $this->assertEquals('user', $this->config->key());

        /* Step beyond the end and confirm the result */
        $this->config->next();
        $this->assertNull($this->config->key());
    }

    /**
     * @covers Noodlehaus\AbstractConfig::next()
     */
    public function testNext()
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();

        /* Step through each of the other elements of the test config */
        $this->assertEquals($this->config['port'], $this->config->next());
        $this->assertEquals($this->config['servers'], $this->config->next());
        $this->assertEquals($this->config['application'], $this->config->next());
        $this->assertEquals($this->config['user'], $this->config->next());

        /* Step beyond the end and confirm the result */
        $this->assertFalse($this->config->next());
    }

    /**
     * @covers Noodlehaus\AbstractConfig::rewind()
     */
    public function testRewind()
    {
        /* Rewind from somewhere out in the array */
        $this->config->next();
        $this->config->next();
        $this->assertEquals($this->config['host'], $this->config->rewind());

        /* Rewind again from the beginning of the array */
        $this->assertEquals($this->config['host'], $this->config->rewind());
    }

    /**
     * @covers Noodlehaus\AbstractConfig::valid()
     */
    public function testValid()
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();
        $this->assertTrue($this->config->valid());

        /* Step through each of the other elements of the test config */
        $this->config->next();
        $this->assertTrue($this->config->valid());
        $this->config->next();
        $this->assertTrue($this->config->valid());
        $this->config->next();
        $this->assertTrue($this->config->valid());
        $this->config->next();
        $this->assertTrue($this->config->valid());

        /* Step beyond the end and confirm the result */
        $this->config->next();
        $this->assertFalse($this->config->valid());
    }

    /**
     * Tests to verify that Iterator is properly implemented by using a foreach
     * loop on the test config
     */
    public function testIterator()
    {
        /* Create numerically indexed copies of the test config */
        $expectedKeys = ['host', 'port', 'servers', 'application', 'user'];
        $expectedValues = [
            'localhost',
            80,
            ['host1', 'host2', 'host3'],
            [
                'name'   => 'configuration',
                'secret' => 's3cr3t',
                'runtime' => null,
            ],
            null
        ];

        $idxConfig = 0;

        foreach ($this->config as $configKey => $configValue) {
            $this->assertEquals($expectedKeys[$idxConfig], $configKey);
            $this->assertEquals($expectedValues[$idxConfig], $configValue);
            $idxConfig++;
        }
    }

    public function testGetShouldNotSet()
    {
        $this->config->get('invalid', 'default');
        $actual = $this->config->get('invalid', 'expected');
        $this->assertSame('expected', $actual);
    }

    /**
     * @covers Noodlehaus\AbstractConfig::remove()
     */
    public function testRemove()
    {
        $this->config->remove('application');
        $this->assertNull($this->config['application']);
    }

    public function testDefaultOptionsSetOnInstantiationDoNotOverwriteSections()
    {
        $config = new SimpleConfig(
            [
                'application' => ['secret' => 'verysecret']
            ]
        );
        $this->assertEquals('configuration', $config->get('application.name'));
        $this->assertEquals('verysecret', $config->get('application.secret'));
    }    
}

