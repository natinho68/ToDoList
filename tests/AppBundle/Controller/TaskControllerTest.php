<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Task;

class TaskControllerTest extends WebTestCase
{
    public function test_listAction()
    {

    }

    public function test_createAction()
    {
        $task = new Task();

    }
}