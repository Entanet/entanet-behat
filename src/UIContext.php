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
     * @Then I click table header :header
     */
    public function iClickTableHeader($header)
    {
        $row = $this->getPage()->find('css', sprintf('table th:contains("%s")', $header));
        $this->waitForJavaScript();
        $row->press();
    }

    /**
     * @Given /^The element "(?P<selector>[^"]*)" should have a css property "(?P<property>[^"]*)" with a value of "(?P<value>[^"]*)"$/
     *
     */
    public function assertElementHasCssValue($selector, $property, $value)
    {
        $page = $this->getPage();
        $element = $page->find('css', $selector);

        if (empty($element)) {
            $message = sprintf('Could not find element using the selector "%s"', $selector);
            throw new \Exception($message);
        }
        $style = $this->elementHasCSSValue($element, $property, $value);
        if (empty($style)) {
            $message = sprintf('The property "%s" for the selector "%s" is not "%s"', $property, $selector, $value);
            throw new \Exception($message);
        }
    }

    /**
     * Determine if a Mink NodeElement contains a specific css rule attribute value.
     *
     * @param NodeElement $element
     *   NodeElement previously selected with $this->getSession()->getPage()->find().
     * @param string $property
     *   Name of the CSS property, such as "visibility".
     * @param string $value
     *   Value of the specified rule, such as "hidden".
     *
     * @return NodeElement|bool
     *   The NodeElement selected if true, FALSE otherwise.
     */
    protected function elementHasCSSValue($element, $property, $value)
    {
        $exists = FALSE;
        $style = $element->getAttribute('style');
        if ($style) {
            if (preg_match("/(^{$property}:|; {$property}:) ([a-z0-9]+);/i", $style, $matches)) {
                $found = array_pop($matches);
                if ($found == $value) {
                    $exists = $element;
                }
            }
        }
        return $exists;
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