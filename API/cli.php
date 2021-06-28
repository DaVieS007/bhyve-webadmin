#!/usr/local/bin/php

<?php
$API_KEY = "208C694F9CBD2B0B47F8E4EC7C0D2A5FB3B29984802E3E049A73A2011CB93BDC"; // CHANGE THIS !!
$API_HOST = "localhost";
$API_PORT = "8628";

error_reporting(E_ALL^E_NOTICE);
/** REQUIREMENTS
 * PHP-SOCKETS
 * PHP-ZLIB
 * PHP-JSON
 * PHP-HASH
 * PHP-OPENSSL
 */

    $API_KEY = hex2bin($API_KEY);
    $DP = new DProtocol($API_HOST,$API_PORT);
    $DP->enableAES($API_KEY);
    $res = $DP->query("PING",array(""));
    if($res["code"] != "PONG")
    {
        echo("\n\n -> Error: Unable to contact with VM Backend module\n");
        exit(10);
    }

    $res = $DP->query("CLIENT",$argv);
    process($res);

    /** PROCESS **/
    function process($res)
    {
        if($res["code"] == "OK")
        {
            if(is_array($res["meta"]) && count($res["meta"]) > 0)
            {
                echo("Command was success, results:\n");
                print_r($res["meta"]);    
            }
            else
            {
                echo("Command was success\n");
            }

            exit(0);
        }
        else
        {
            echo("There was an error: ".$res["code"]."\n");
            if(isset($res["meta"]["cli_error"]))
            {
                foreach($res["meta"]["cli_error"] as $k=>$v)
                {
                    echo("      ".$v."\n");
                }
            }
            else if(isset($res["meta"]["error"]))
            {
                echo("Error: ".$res["meta"]["error"]."\n");
            }

            exit(1);
        }

    }
    /** PROCESS **/

    /** DPROTOCOL CLASS **/
    class DProtocol /** VERSION 2.0 **/
    {
        var $protocol_version = "2";
        var $host = "";
        var $port = "";
        var $sock;
        var $timeout = 5;
        var $key = NULL;
        var $data;

        /** CONSTRCUTOR **/
        function __construct($host,$port,$timeo = 0)
        {
            $this->host = $host;
            $this->port = $port;

            if($timeo > 0)
            {
                $this->timeout = $timeo;
            }
        }
        /** CONSTRCUTOR **/

        /** DESTRUCTOR **/
        function __destruct()
        {
            if(is_resource($this->sock))
            {
                fclose($this->sock);
            }
        }
        /** DESTRUCTOR **/

        /** _FREAD **/
        function _fread($sock,$len)
        {
            $ret = "";
            $wlen = 0;

            while(true)
            {
                $delta = $len - $wlen;
                if($delta <= 0)
                {
                    break;
                }

                $tmp = fread($sock,$delta);    
                if(!$tmp)
                {
                    break;
                }

                $ret .= $tmp;
                $wlen += strlen($tmp);
            }

            return $ret;
        }
        /** _FREAD **/

        /** EC **/
        function ec($string,$iv)
        {
            $key = hash("md5",$this->key,true);
            $encrypted = openssl_encrypt($string, "AES-128-CBC", $key, $options=OPENSSL_RAW_DATA, $iv);
            return $encrypted;
        }
        /** EC **/
    
        /** DEC **/
        function dec($string,$iv)
        {
            $key = hash("md5",$this->key,true);
            $result = openssl_decrypt($string, "AES-128-CBC", $key, $options=OPENSSL_RAW_DATA, $iv);
            return $result;
        }
        /** DEC **/
    

        /** SETTIMEOUT **/
        function setTimeout($timeo)
        {
            $this->timeout = $timeo;
        }
        /** SETTIMEOUT **/

        /** FWRITE_STREAM **/
        function fwrite_stream($fp, $string) 
        {
            for ($written = 0; $written < strlen($string); $written += $fwrite) 
            {
                $fwrite = fwrite($fp, substr($string, $written));
                if ($fwrite === false) 
                {
                    return $written;
                }
            }
            return $written;
        }
        /** FWRITE_STREAM **/

        /** ENABLEAES **/
        function enableAES($key)
        {   
            $this->key = $key;
        }
        /** ENABLEAES **/

        /** QUERY **/
        function query($code,$arr,$data = NULL)
        {
            $this->data = array();
            if(!is_resource($this->sock))
            {
                $this->sock = stream_socket_client("tcp://".$this->host.":".$this->port, $errno, $errstr, $this->timeout);
                if(!$this->sock)
                {
                    return false;
                }    
            }

            if($this->key)
            {
                $iv = hash("md5",$code.mt_rand(0,9999999).time(),true);
            }

            if($this->protocol_version > 1)
            {
                $arr = serialize($arr);
            }
            else
            {
                $arr = json_encode($arr);
            }

            $arr = gzencode($arr,1);
            if($this->key)
            {
                $arr = $this->ec($arr,$iv);
            }

            if($data != "")
            {
                $data = gzencode($data,1);
                if($this->key)
                {
                    $data = $this->ec($data,$iv);
                }
            }


            $header = "D".$code.".".$this->protocol_version.".".strlen($arr).".".strlen($data).".";
            if($this->key)
            {
                $header .= "1";
            }
            else
            {
                $header .= "0";
            }
            $header .= "\n";

            $this->fwrite_stream($this->sock,$header);
            if($this->key)
            {                
                $this->fwrite_stream($this->sock,$iv);
            }

            $this->fwrite_stream($this->sock,$arr);
            if($data)
            {
                $this->fwrite_stream($this->sock,$data);
            }

            /** RESPONSE **/
            $rheader = trim(fgets($this->sock));
            $this->data["header"] = substr($rheader,1);
            if(substr($this->data["header"],0,1) == ":")
            {
                $this->data["code"] = "int_error";
                $this->data["error"] = substr($this->data["header"],1);
                return $this->data;
            }

            $ex = explode(".",$this->data["header"]);
            $this->data["code"] = $ex[0];
            $this->data["protocol_version"] = $ex[1];
            $this->data["meta_len"] = $ex[2];
            $this->data["data_len"] = $ex[3];
            $this->data["crypt"] = $ex[4];

            if($this->data["crypt"] == "1")
            {
                $iv = $this->_fread($this->sock,16);
            }

            $META = $this->_fread($this->sock,$this->data["meta_len"]);

            if($this->data["data_len"] > 0)
            {
                $DATA = $this->_fread($this->sock,$this->data["data_len"]);
            }

            if($this->data["crypt"] == "1")
            {
                $META = $this->dec($META,$iv);
                if($this->data["data_len"])
                {
                    $DATA = $this->dec($DATA,$iv);
                }
            }

            if($this->data["protocol_version"] == "1")
            {
                $this->data["meta"] = json_decode(gzdecode($META), true);
            }
            else if($this->data["protocol_version"] == "2")
            {
                $this->data["meta"] = unserialize(gzdecode($META));
            }

            if($this->data["data_len"] > 0)
            {
                $this->data["data"] = gzdecode($DATA);
            }
            /** RESPONSE **/

            return $this->data;
        }
        /** QUERY **/
    }
    /** DPROTOCOL CLASS **/
?>
