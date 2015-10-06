<?php

// handler for HERA librarian RPCs

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

require_once("hl_db.inc");

init_db();

function error($msg) {
    $reply = new StdClass;
    $reply->success = 0;
    $reply->message = $msg;
    echo json_encode($reply);
}

function success() {
    $reply = new StdClass;
    $reply->success = true;
    echo json_encode($reply);
}

function create_file($req) {
    $user = user_lookup_auth($req->authenticator);
    if (!$user) {
        echo "foo"; exit;
        error("auth failure");
        return;
    }
    $req->create_time = time();
    $req->user_id = $user->id;
    if (!file_insert($req)) {
        error(db_error());
        return;
    }
    success();
}

function create_file_instance($req) {
    $user = user_lookup_auth($req->authenticator);
    if (!$user) {
        error("auth failure");
        return;
    }
    $file = file_lookup_name($req->file_name);
    if (!$file) {
        error("bad file name");
        return;
    }
    $site = site_lookup_name($req->site_name);
    if (!$site) {
        error("bad site name");
        return;
    }
    $store = store_lookup_name($site->id, $req->store_name);
    if (!$store) {
        error("bad store name");
        return;
    }
    $req->file_id = $file->id;
    $req->store_id = $store->id;
    $req->create_time = time();
    $req->user_id = $user->id;
    if (!file_instance_insert($req)) {
        error(db_error());
        return;
    }
    success();
}

$req = json_decode($_POST['request']);
switch ($req->operation) {
case 'create_file': create_file($req); break;
case 'create_file_instance': create_file_instance($req); break;
default: error("unknown op $req->operation");
}

?>
