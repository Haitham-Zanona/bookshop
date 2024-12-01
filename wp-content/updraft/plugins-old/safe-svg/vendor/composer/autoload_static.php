<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaee24f057dabdc3d081112793cdea14a
{
    public static $files = array (
        '2cffec82183ee1cea088009cef9a6fc3' => __DIR__ . '/..' . '/ezyang/htmlpurifier/library/HTMLPurifier.composer.php',
    );

    public static $prefixLengthsPsr4 = array (
        'e' => 
        array (
            'enshrined\\svgSanitize\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'enshrined\\svgSanitize\\' => 
        array (
            0 => __DIR__ . '/..' . '/enshrined/svg-sanitize/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'HTMLPurifier' => 
            array (
                0 => __DIR__ . '/..' . '/ezyang/htmlpurifier/library',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaee24f057dabdc3d081112793cdea14a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaee24f057dabdc3d081112793cdea14a::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitaee24f057dabdc3d081112793cdea14a::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitaee24f057dabdc3d081112793cdea14a::$classMap;

        }, null, ClassLoader::class);
    }
}