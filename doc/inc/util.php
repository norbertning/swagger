<?php

function getClassMap($path)
{
    $map = [];
    $dir = dir($path);
    while (false !== ($entry = $dir->read())) {
        if ($entry[0] == '.') {
            continue;
        }
        $baseName = basename($entry, '.php');
        $file = $path . '/' . $entry;
        if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
            $map[$baseName] = $file;
        } elseif (is_dir($file)) {
            $map[$baseName] = getClassMap($file);
        }
    }
    return $map;
}

/**
 * 用于yii2 获取modules下的目录
 * @param $path
 * @return array
 */
function getModulesMap($path)
{
    $map = [];
    $dir = dir($path);
    while (false !== ($entry = $dir->read())) {
        if ($entry[0] == '.') {
            continue;
        }
        basename($entry, '.php');
        $file = $path . '/' . $entry;
        if (is_dir($file)) {
            $map[] = $entry;
        }
    }
    return $map;
}