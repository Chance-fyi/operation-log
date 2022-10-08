<?php
/**
 * Created by PhpStorm
 * Date 2022/10/8 9:54
 */

namespace Chance\Log\orm\illuminate;

class MySqlConnection extends \Illuminate\Database\MySqlConnection
{
    public function query()
    {
        return new Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }
}