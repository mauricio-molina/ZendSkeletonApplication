<?php

namespace Album\Controller;

use Album\Model\Album;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AlbumController extends AbstractActionController
{
    private $album;

    public function __construct(Album $album) {
        $this->album = $album;
    }
    public function indexAction()
    {
        return new ViewModel([
            'albums' => $this->album,
        ]);
    }
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $this->album->setJson($id);

        return new ViewModel([
            'albums' => $this->album,
            'id' => $id,
        ]);
    }
}