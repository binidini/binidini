<?php
#https://github.com/betacie/webhooks

namespace Binidini\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GithubController extends Controller
{
    public function pushAction(Request $request)
    {
        chdir('/var/www/tytymyty');

        system('/usr/bin/env -i /usr/bin/git pull origin develop 2>&1');
        system('/usr/bin/env -i php ./app/console doctrine:schema:update --force');
        system('/usr/bin/env -i php ./app/console assets:install');
        system('/usr/bin/env -i php ./app/console cache:clear --env=prod');
        system('/usr/bin/env -i php ./app/console cache:clear');

        return new JsonResponse(['status' => 'ok']);
    }
}
