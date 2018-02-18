<?php
namespace Facebook\WebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

header("Content-type: text/plain; charset=utf-8");
shell_exec('java -jar bot/selenium.jar &> /dev/null &');

echo
    "     ___________                            " .PHP_EOL.
    "    / _   _     \                           " .PHP_EOL.
    "   | | | ' '__   |                          " .PHP_EOL.
    "   | | | |  _  \ |     LINKEDIN BOT v.1.0   " .PHP_EOL.
    "   | | | | | | | |        by Samuel Faj     " .PHP_EOL.
    "   | |_| |_| |_| |                          " .PHP_EOL.
    "    \___________/                           " .PHP_EOL.
    PHP_EOL;


require_once('vendor/autoload.php');

class LinkedInBot{
    public $cycle   = 0;
    public $likes   = 0;
    public $invites = 0;
    public $accepts = 0;

    public function __construct($username,$password,$maxCycles,$sleepBetweenCycles,$likesOnFeed,$maxNewConnections,$host,$autolike,$autoInvite,$autoAdd){
        $this->host = $host;
        $this->username = $username; 
        $this->password = $password;
        $this->maxCycles   = $maxCycles; 
        $this->likesOnFeed = $likesOnFeed;
        $this->maxNewConnections  = $maxNewConnections;
        $this->sleepBetweenCycles = $sleepBetweenCycles;

        $this->autoAdd  = $autoAdd;
        $this->autolike = $autolike;
        $this->autoInvite = $autoInvite;

        $this->capabilities = DesiredCapabilities::chrome();
        $this->driver       = RemoteWebDriver::create($this->host, $this->capabilities, 5000);
    }

    public function start(){
        $this->getIntoLinkedIn();
        $this->startCycle();
    }

    public function check(){
        $url = $this->driver->getCurrentURL();

        if(strpos($url,'login') !== false){
            $this->driver->findElement(WebDriverBy::id('session_key-login'))->sendKeys($this->username);
            $this->driver->findElement(WebDriverBy::id('session_password-login'))->sendKeys($this->password);
            $this->driver->findElement(WebDriverBy::id('btn-primary'))->click();
        }
    }
    
    public function getIntoLinkedIn(){
        echo 'Logging into LinkedIn...'.PHP_EOL;
        $this->driver->get('https://www.linkedin.com/'); // realizando uma requisição HTTP get na $url
        $this->check();

        $elements =  $this->driver->findElements(WebDriverBy::cssSelector('nav#extended-nav'));
        if(count($elements) == 0){
            try{
                $this->driver->findElement(WebDriverBy::id('login-email'))->sendKeys($this->username);
                $this->driver->findElement(WebDriverBy::id('login-password'))->sendKeys($this->password);
                $this->driver->findElement(WebDriverBy::id('login-submit'))->click();
            } catch (Exception $e) {
                echo '    ERROR - May not be in login page.'.PHP_EOL;
            }
        }
    }

    public function startCycle(){
        while($this->maxCycles == 0 || $this->cycle <= $this->maxCycles) {
            echo PHP_EOL;
            echo $this->cycle.'/'.$this->maxCycles. ' - Cycle Initialized'.PHP_EOL;

            $this->likes = 0;
            $this->autoLike();

            $this->invites = 0;
            $this->accepts = 0;
            $this->autoInvite();


            $this->cycle++;

            if($this->cycle <= $this->maxCycles){
                if(isset($this->sleepBetweenCycles) && $this->sleepBetweenCycles > 0){
                    echo 'Cycle finished.' . PHP_EOL;
                    echo PHP_EOL;
                    echo 'Sleeping ' . $this->sleepBetweenCycles . ' seconds before start another.';
                    sleep($this->sleepBetweenCycles);
                }
            }
        }
    }

    public function autoLike(){
        $hasfeed = true;

        if(!$this->autolike) return false;

        echo '    Getting into Feed...'.PHP_EOL;
        while($hasfeed && ($this->likesOnFeed == 0 || $this->likes <= $this->likesOnFeed)){

            if($this->likes > 0 || $this->driver->getCurrentURL() !== 'https://www.linkedin.com/feed/') {
                $this->driver->get('https://www.linkedin.com/feed/');
            }

            $this->check();

            try{
                $this->driver->wait(10, 500)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(
                        WebDriverBy::cssSelector('article div button.like-button:not(.active):first-of-type')
                    )
                );

                do{
                    try{
                        $elements =  $this->driver->findElements(WebDriverBy::cssSelector('article div button.like-button:not(.active):first-of-type'));
                        foreach ($elements as $i=>$element){
                            if($this->likes > $this->likesOnFeed) continue;

                            if($element->isEnabled() && $element->isDisplayed()){
                                $element->getLocationOnScreenOnceScrolledIntoView();

                                $id = $element->getAttribute('id');
                                $this->driver->executeScript('$("#'.$id.'").click()',array());

                                $this->likes++;
                                echo '        ' . $this->likes . '/' . $this->likesOnFeed . ' => Linking post... ' . PHP_EOL;

                                sleep(rand(5,20));
                            }
                        }
                    }catch (Exception $e2){ echo '        ERROR - ' . $e2 . PHP_EOL; }

                }while(count($elements) > 0 && $this->likes <= $this->likesOnFeed);
            }catch (\Exception $e){ echo '        ERROR - ' . $e . PHP_EOL;  }

            if(count($elements) == 0) $hasfeed = false;
        }

        echo PHP_EOL;
    }
    public function autoInvite(){
        while($this->maxNewConnections == 0 || $this->invites <= $this->maxNewConnections){
            echo '    Getting into My Network...'.PHP_EOL;

            $this->driver->get('https://www.linkedin.com/mynetwork/');

            $this->check();

            try{
                $this->driver->wait(10, 500)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.mn-pymk-list__card'))
                );

                $this->autoAdd();

                if(!$this->autoInvite) return false;

                do{
                    $elements =  $this->driver->findElements(WebDriverBy::cssSelector('button.artdeco-dismiss'));
                    if(count($elements) > 0) $this->driver->executeScript("$('button.artdeco-dismiss').click();",array());

                    $elements =  $this->driver->findElements(WebDriverBy::cssSelector('.mn-pymk-list__card'));
                    foreach ($elements as $i=>$element){
                        if($this->invites > $this->maxNewConnections) continue;

                        try{
                            $user = $element->findElement(WebDriverBy::cssSelector('span.mn-person-info__name'));
                            echo '        '.$this->invites.'/'.$this->maxNewConnections.' => Sending an invite to '.$user->getText().'...'.PHP_EOL;

                            $button = $element->findElement(WebDriverBy::cssSelector('.button-secondary-small'));
                            $button->getLocationOnScreenOnceScrolledIntoView();
                            $button->click();
                        }catch (\Exception $e){ echo '        ERROR - ' . $e . PHP_EOL; }

                        sleep(rand(5,10));

                        $this->invites++;
                    }
                }while(count($elements) > 0 && $this->invites <= $this->maxNewConnections);
            }catch (\Exception $e){ echo '        ERROR - ' . $e . PHP_EOL;  }


            echo PHP_EOL;
            echo '    No more possible connections in this page. ' . PHP_EOL;
            echo '        => Reloading...' . PHP_EOL;
            echo PHP_EOL;
        }
    }
    public function autoAdd(){
        if(!$this->autoAdd) return false;

        $elements =  $this->driver->findElements(WebDriverBy::cssSelector('.mn-invitation-card'));
        foreach ($elements as $i=>$element){
            echo '        '.$this->accepts.'/'.$this->accepts.' => Accepting user invite.'.PHP_EOL;

            $id = $element->getAttribute('id');
            $this->driver->executeScript('$("#'.$id.' button[data-control-name=\"accept\"]").click()',array());

            $this->accepts++;
            sleep(rand(5,10));
        }
    }
}