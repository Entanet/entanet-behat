<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Laracasts\Behat\Context\DatabaseTransactions;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Element\NodeElement;


class UIContext extends MinkContext implements Context
{

    private $javascriptWait = 2500;

    /**
     * @BeforeScenario
     */
    public function setUp()
    {

    }


    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }

    /**
     * @return \Behat\Mink\Element\DocumentElement
     */
    private function getPage()
    {
        return $this->getSession()->getPage();
    }


    public function waitForJavaScript()
    {
        $this->getSession()->wait($this->javascriptWait);
    }

    /**
     * Pauses the scenario until the user presses a key. Useful when debugging a scenario.
     *
     * @Then /^break$/
     */
    public function iPutABreakpoint()
    {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {
        }
        fwrite(STDOUT, "\033[u");

        return;
    }

    /**
     * @Then I OK the JavaScript confirmation
     */
    public function iConfirmTheJsDialogue()
    {
        $selenuim = new \Behat\Mink\Driver\Selenium2Driver();
        $selenuim->getWebDriverSession()->accept_alert();
    }
}