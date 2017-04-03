<?php

namespace Album\Model;

use Zend\Cache\Storage\Adapter\Memcached;

class Album
{
    public $ec2;

    public $rds;

    public function __construct()
    {
        $this->ec2 = NULL;
        $this->rds = NULL;
        $this->s3 = NULL;
        $this->sqs = NULL;
    }

    public function setJson($id) {
        $cache = new Memcached();
        $cache->getOptions()->setServers('localhost', 11211);
        $cache->getOptions()->setTtl(60);

        $aws_components = [
            'ec2',
            'rds',
            's3',
            'sqs',
            'elasticache'
        ];

        print "Available:" . $cache->getTotalSpace() . "<br>";
        print "Open:" . $cache->getAvailableSpace() . "<br><br>";

        foreach ($aws_components as $component) {
            $key = $id . '_' . $component;

            if ($cache->hasItem($key)) {
                $json = $cache->getItem($key);

                $this->{$component} = $json;
//                print $key . "memcached item exists." . "<br><br>";
            }
            else {
                // @TODO Replace this file open stuff with an AWS call.
                $filename = $_SERVER['DOCUMENT_ROOT'] . '/' . $key . '.json';
                $handle = fopen($filename, 'r');
                $data = fread($handle,filesize($filename));

                $cache->setItem($key, $data);
                $json = $cache->getItem($key);

                $this->{$component} = $json;
             //   print "memcached item expired or doesn't exist" . "<br><br>";
            }
        }
    }
}