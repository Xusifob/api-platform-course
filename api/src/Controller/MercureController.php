<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MercureController extends AbstractController
{



    public function mercureTestAction() : Response
    {

        return $this->render("mercure/mercure.html.twig");

    }

}
