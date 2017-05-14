<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require_once('src/autoload.php');

$faker = Faker\Factory::create();

$name = $faker->firstName;
$ip   = $faker->ipv4;
$lorem = $faker->paragraphs($nb = 3, $asText = false);

echo "Name: $name<br>";
echo "Ip: $ip<br>";
echo "Lorem:<pre>";
print_r($lorem);
echo "</pre>";