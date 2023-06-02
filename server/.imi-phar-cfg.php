<?php

declare(strict_types=1);

// 以下输入的路径，无特殊说明都请写入当前工作目录中的相对路径
return [
    // 构建出的 phar 文件
    'output'       => 'build/imi.phar',

    // 参与打包的文件
    // 可选值：
    //   - '*'自动包含根目录下的 *.php、*.macro 文件。（默认）
    //   - 空数组不包含任何文件。
    //   - 定义数组并填入文件名（仅限于当前目录下的文件）。
    'files'        => '*',

    // 参与打包的目录
    'dirs'         => [
        // 参与打包的目录
        // 可选值：
        //   - '*'自动包含根目录除`vendor`以为的目录。（默认）
        //   - 空数组不包含任何目录。
        //   - 定义数组并填入目录名（仅限于当前目录下的目录名）。
        'in'           => '*',
        // 要排除的的目录，仅对 dirs 扫描到的内容有作用。
        'excludeDirs'  => [
            '.vscode',
            'bin',
            'dev',
            'build',
            '.runtime',
        ],
        // 要排除的的文件，仅对 dirs 扫描到的内容有作用。
        'excludeFiles' => [],
    ],

    // 包含 vendor 目录
    // 可选值：
    //   - true  : 打包 vendor 中的文件
    //   - false : 不打包 vendor 中的文件
    //   - symfony/finder 实例 : 完全自定义如何获得文件
    // 无特殊情况勿动
    'vendorScan'   => true,

    // 传入 symfony/finder 实例，支持多个，完全自定义扫描的内容。
    'finder'       => [],

    // 是否转存构建时的 git 信息
    'dumpGitInfo'  => true,

    // 自定义启动入口
    // 默认读取命令行 container 参数生成入口代码
    // 如果指定一个有效的 php 文件文件则可以完全控制入口
    // 如果命令行传入为值，则覆盖该选项值
    // 可选值：
    //   - swoole
    //   - workerman
    //   - roadrunner
    //   - 当前目录下的一个有效 php 文件
    'bootstrap'    => null,

    // 压缩算法，一旦启用压缩，则执行环境也必须加载对应的依赖库
    // 由于 PHP 内核 BUG，该选项暂时屏蔽
    // 可选值：
    //   - \Phar::NONE : 不压缩
    //   - \Phar::GZ   : 必须启用扩展 zlib
    //   - \Phar::BZ2  : 必须启用扩展 bzip2
    'compression'  => \Phar::NONE,

    // 构建配置
    'build'             => [
        'before' => static function () {
            // 构建前执行的代码
        },
        'after'  => static function () {
            // 构建后执行的代码
        },
    ],

    // 资源文件配置
    'resources'         => [
        'files'             => [
            // 'a.txt', // 将 a.txt 输出到 build/a.txt
            // 'a.txt' => 'b.txt', // 将 a.txt 输出到 build/b.txt
            '.env.release' => '.env',
        ],
        // 可选值：
        //   - 空数组不包含任何目录。
        //   - 定义数组并填入目录名（仅限于当前目录下的目录名）。
        'in'                => [],
        // 要排除的的目录，仅对 dirs 扫描到的内容有作用。
        'excludeDirs'       => [],
        // 要排除的的文件，仅对 dirs 扫描到的内容有作用。
        'excludeFiles'      => [],
    ],
];
