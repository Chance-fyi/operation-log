<?php

use Faker\Factory;

const columnComment = [
    'name' => '姓名',
    'phone' => '手机号',
    'email' => '邮箱',
    'sex' => '性别',
    'age' => '年龄',
];

function createLog(array $data, $batch = false): string
{
    $log = sprintf('创建 用户 (id:%s)：', $data[0] ?? '');
    if ($batch) {
        $log = '批量创建 用户：';
    }
    unset($data[0]);
    foreach ($data as $key => $val) {
        $val = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
        $log .= (columnComment[$key] ?? $key) . "：{$val}，";
    }

    return mb_substr($log, 0, mb_strlen($log, 'utf8') - 1, 'utf8') . PHP_EOL;
}

function batchCreateLog(array $data): string
{
    $log = '';
    foreach ($data as $datum) {
        $log .= createLog($datum, true);
    }

    return trim($log);
}

function updateLog(array $old, array $new, $batch = false): string
{
    $log = sprintf('修改 用户 (id:%s)：', $old['id']);
    if ($batch) {
        $log = sprintf('批量修改 用户 (id:%s)：', $old['id']);
    }
    $old = array_map(function ($value) {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }, $old);
    $new = array_map(function ($value) {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }, $new);
    $diffKeys = diffKeys($old, $new);
    if (empty($diffKeys)) {
        return '';
    }

    foreach ($diffKeys as $key) {
        $log .= (columnComment[$key] ?? $key) . "由：{$old[$key]} 改为：{$new[$key]}，";
    }

    return mb_substr($log, 0, mb_strlen($log, 'utf8') - 1, 'utf8') . PHP_EOL;
}

function batchUpdateLog(array $old, array $new): string
{
    $log = '';
    foreach ($old as $item) {
        $log .= updateLog($item, $new, true);
    }

    return $log;
}

function deleteLog($data, $batch = false): string
{
    $log = sprintf('删除 用户 (id:%s)：', $data['id']);
    if ($batch) {
        $log = sprintf('批量删除 用户 (id:%s)：', $data['id']);
    }
    unset($data['id']);
    $data = array_map(function ($value) {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }, $data);
    foreach ($data as $key => $val) {
        $log .= (columnComment[$key] ?? $key) . "：{$val}，";
    }

    return mb_substr($log, 0, mb_strlen($log, 'utf8') - 1, 'utf8') . PHP_EOL;
}

function batchDeleteLog($data): string
{
    $log = '';
    foreach ($data as $item) {
        $log .= deleteLog($item, true);
    }

    return $log;
}

function mockData(): array
{
    $faker = Factory::create();

    return [
        'name' => $faker->name(),
        'phone' => $faker->phoneNumber(),
        'email' => $faker->email(),
        'sex' => rand(0, 1),
        'age' => rand(10, 20),
    ];
}

function mockDatas(): array
{
    return [
        mockData(),
        mockData(),
        mockData(),
    ];
}

function diffKeys(array $old, array $new): array
{
    return array_keys(array_diff_assoc($old, array_merge($old, $new)));
}
