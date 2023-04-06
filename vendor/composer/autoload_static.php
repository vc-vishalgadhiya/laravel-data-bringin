<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4452376d043140fdd9084d0013402502
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Vcian\\LaravelDrift\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Vcian\\LaravelDrift\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4452376d043140fdd9084d0013402502::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4452376d043140fdd9084d0013402502::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4452376d043140fdd9084d0013402502::$classMap;

        }, null, ClassLoader::class);
    }
}