<?php

namespace Album\Model;

use Zend\Cache\Storage\Adapter\Memcached;

CONST AWS_SERVICES = ['ec2','rds','s3','sqs','elasticache'];
CONST CMS_BRANDS = ['bravo','chiller','cnbc','eonline','esquire','msnbc','nbcuniverso','oxygen','seeso','sprout','syfy','telemundo','usa'];

class Album
{
    public $ec2;

    public $rds;

    public function __construct($ec2 = NULL, $rds = NULL, $s3 = NULL, $sqs = NULL, $elasticache = NULL)
    {
        $this->ec2 = $ec2;
        $this->rds = $rds;
        $this->s3 = $s3;
        $this->sqs = $sqs;
        $this->elasticache = $elasticache;
    }

    public function setJson($id) {
        $cache = new Memcached();
        $cache->getOptions()->setServers('localhost', 11211);
        $cache->getOptions()->setTtl(60);

        foreach (AWS_SERVICES as $service) {
            $key = $id . '_' . $service;
print "oh" . "\n";
print "my" . "\n";

            if ($cache->hasItem($key)) {
                $json = $cache->getItem($key);

                $decoded_json = json_decode($json);
                $this->{$service} = $decoded_json;
//                print $key . "memcached item exists." . "<br><br>";
            }
            else {
                // @TODO Replace this file open stuff with an AWS call.
                $filename = $_SERVER['DOCUMENT_ROOT'] . '/' . $key . '.json';
                $handle = fopen($filename, 'r');
                $data = fread($handle,filesize($filename));

                $cache->setItem($key, $data);
                $json = $cache->getItem($key);

                $decoded_json = json_decode($json);
                $this->{$service} = $decoded_json;
//                print "memcached item expired or doesn't exist" . "<br><br>";
            }
        }
    }

    public function envJson($env, $id) {
        $this->{$env} = NULL;
        foreach (AWS_SERVICES as $aws_service) {
            if ($this->$aws_service) {
                if ($aws_service == 'ec2') {
                    $data = NULL;
                    foreach ($this->$aws_service as $object => $instance) {
                        if ($instance->Environment == $env) {
                            $data[] = $instance;
                        }
                    }
                }
                elseif ($aws_service == 'rds') {
                    $data = NULL;
                    // Account for naming conventions with load environment.
                    $env2 = ($env == 'load' ? 'lt': '');
                    foreach ($this->$aws_service as $object => $instance) {
                        if (strpos($instance->Identifier, $env) !== false || strpos($instance->Identifier, $env2) !== false) {
                            $data[] = $instance;
                        }
                    }
                    if ($env == 'load') {

                    }
                }
                elseif ($aws_service == 'sqs') {
                    $data = NULL;
                    foreach ($this->$aws_service->QueueUrls as $var) {

                        if (strpos(strtolower($var), $env) !== FALSE) {
                            $new_var = explode("/", $var);
                            $data->QueueUrls[] = end($new_var);
                        }
                    }

                    if ($id == 'cms') {
                        // TODO: loop through brands for this env.
//                        foreach (CMS_BRANDS as $cms_brand) {
//                            $response = file_get_contents("http://" . $env . ".tvecms." . $cms_brand . ".nbcuni.com/sqs_app_config");
//                            if ($response !=) {
//
//                            }
//                            $data->queues[] = json_decode($response);
//                        }
                        $data->queues[] = json_decode(file_get_contents("http://tve_ott_cms-1858.bravo.pr.tve_ott_cms.nbcuni.com/sqs_app_config"));
                    }
                }

                // Set the data for the aws service and environment.
                if ($data != NULL) {
                    $this->$env->$aws_service = $data;
                }
            }
        }
    }
}
