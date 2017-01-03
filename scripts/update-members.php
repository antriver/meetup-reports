<?php

require dirname(__DIR__).'/vendor/autoload.php';

$meetup = new \Meetup\Meetup();

$meetup->members->update();
