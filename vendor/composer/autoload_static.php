<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite612c6110788118377b24a68a3d94030
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Predis\\' => 7,
        ),
        'L' => 
        array (
            'LINE\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Predis\\' => 
        array (
            0 => __DIR__ . '/..' . '/predis/predis/src',
        ),
        'LINE\\' => 
        array (
            0 => __DIR__ . '/..' . '/linecorp/line-bot-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite612c6110788118377b24a68a3d94030::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite612c6110788118377b24a68a3d94030::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
