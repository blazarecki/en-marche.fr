<?php

namespace AppBundle\Controller\EnMarche;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/espace-membres-conseil")
 * @Security("is_granted('ROLE_BOARD_MEMBER')")
 */
class BoardController extends Controller
{
    /**
     * @Route("/", defaults={"_enable_campaign_silence"=true}, name="app_board_search")
     * @Method("GET")
     */
    public function searcAction()
    {
        return $this->render('board/search.html.twig');
    }

    /**
     * @Route("/profils-sauvegardes", defaults={"_enable_campaign_silence"=true}, name="app_board_saved_profile")
     * @Method("GET")
     */
    public function savedProfilAction()
    {
        return $this->render('board/saved_profile.html.twig');
    }
}
