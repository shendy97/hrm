<?php

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;

class BaseDataTable extends DataTable
{

    protected $company;
    public $user;
    public $domHtml;

    public function __construct()
    {
        $this->company = company();
        $this->user = user();
        $this->domHtml = "<'row'<'col-sm-12'tr>><'d-flex'<'flex-grow-1'l><i><p>>";
    }

}
