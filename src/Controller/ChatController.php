<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
	/**
	 * @Route("/chat", name="chat")
	 */
	public function chat()
	{
		return $this->render('chat/index.html.twig', []);
	}
}