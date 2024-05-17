<?php
namespace Rytisluko;

class View extends \TypeRocket\Template\View
{
    public function init()
    {
        $this->setFolder(TYPEROCKET_PLUGIN_RYTISLUKO_VIEWS_PATH);
    }
}