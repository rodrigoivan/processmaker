package com.colosa.qa.automatization;

import java.util.List;
import java.util.Random;
//import java.util.current.TimeUnit;

import org.openqa.selenium.By;
import org.openqa.selenium.By.ByPartialLinkText;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.remote.DesiredCapabilities;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.firefox.FirefoxProfile;
import com.colosa.qa.automatization.*;
import java.net.URL;

public class InputDocsFunctionalTest{

	static WebDriver driver;
	public static void main(String[] args) throws Exception{
		//WebDriver driver=FirefoxDriver();
        WebDriver driver= new ChromeDriver();
        WebElement wx = null;
        WebElement element = null;
       WebElement wx1=null;
     
        //Actions action = new Actions(this.driver);
        WebDriverFunctionsPMOS2 pm = new WebDriverFunctionsPMOS2(driver, 0);
        driver.get("http://192.168.11.132");
        pm.login("admin", "admin", "wsqa");
        pm.openProcess("9570"); 
        //pm.selectTaskContextMenuOption("Task 1", "Steps");
        

        element=driver.findElement(By.id("INPUTDOCS"));
        element.click();
        element=driver.findElement(By.id("form[MNU_ADD]"));
        element.click();
        element=driver.findElement(By.id("form[INP_DOC_TITLE]"));
        element.sendKeys("test input docs");
        element=driver.findElement(By.id("form[INP_DOC_DESTINATION_PATH]"));
        element.sendKeys("Files_@#USR_USERNAME");
        element=driver.findElement(By.id("form[ACCEPT]"));
        element.click();
        element=driver.findElement(By.xpath("html/body/div[1]/div[1]/div[1]/div[2]"));
        element.click();
        //asigna un documento automaticamente
        pm.selectTaskContextMenuOption("Task 1", "Steps");
        element=driver.findElement(By.id("form[MNU_ADD]"));
        element.click();
        element=driver.findElement(By.xpath(".//*[@id='table[cHRlVjNLT2lwNlRWNUtUTTFxdWEyNTNPbGMrVnJuS2hySjNj]']/tbody/tr[5]/td[8]/a"));
        element.click();
        element=driver.findElement(By.xpath("html/body/div[1]/div[1]/div[1]/div[2]"));
          element.click();
           pm.goSection("HOME");
        //iniciar caso
        pm.startCase("9570 (Task 1)");
        driver.switchTo().frame("openCaseFrame");
        element=driver.findElement(By.id("form[MNU_NEW]"));
        element.click();
        element=driver.findElement(By.id("form[APP_DOC_FILENAME]"));
        element.sendKeys("C:\\Users\\support\\Downloads\\3537779935078c9b1a2e1e5017999435_1 (4).doc");
        element=driver.findElement(By.id("form[SAVE]"));
        element.click();
        element=driver.findElement(By.id("form[BTN_SUBMIT]"));
          element.click();
          element=driver.findElement(By.id("btnContinue"));
          element.click();
       //element=driver.findElement(By.className("panel_close___processmaker"));
        //element.click();
        //pm.selectMenuTreePanelOption("Documents/");
       //seleccionar archivo a descargar
        driver.switchTo().frame("casesSubFrame");
       element=driver.findElement(By.xpath(".//*[@id='ext-gen66']/div[6]/table/tbody/tr[1]/td[1]/div/b"));
       element.click();

        wx=driver.findElement(By.id("tb_download"));
        WebElement w1=wx.findElement(By.xpath("tbody/tr[2]/td[2]/em/button"));
        w1.click();
//seleccion de documento a eliminar
        element=driver.findElement(By.xpath(".//*[@id='ext-gen66']/div[2]/table/tbody/tr[1]/td[1]/div/b"));
        element.click();
        //eliminar documento
        wx=driver.findElement(By.id("tb_delete"));
        WebElement w2=wx.findElement(By.xpath("tbody/tr[2]/td[2]/em/button"));
        w2.click();
         //eliminacion de documentos
        element=driver.findElement(By.xpath(".//*[@id='ext-comp-1112']/tbody/tr[2]/td[2]"));
        element.click(); 

         
        
    
     // WebElement document=driver.findElement(By.id("tb_download"));
      //List<WebElement> w1=document.findElement(By.xpath("table/tbody/tr[1]/tr[2]/td[1]/td[2]/em"));
       /* pm.goSection("HOME");
        WebElement element_2=driver.findElement(By.id("CASES_START_CASE"));
        element_2.click();
        element_2=driver.findElement(By.id("9570 (Task 1)"));
        element_2.click();
          //pm.startCase("task 1")

      */
        //WebElement we=driver.findElement(By.id("menuTreePanel"));
        //List<WebElement> wl=we.findElement(By.xpath("div/div[1]/div/di[1]/div[2]/div[3]/div[4]/ul/li/li[1]/div/div[1]"));


      //  for(WebElement we2:wl)
        //{
          //  if(we2.getElement(By.xpath("table/tbody/tr[1]/td[1]/td[2]/td[3]/td[4]/td[5]/div")).getText()=="9570")
            //{
              // we = we2;
            //break;
            //}
        //}
        //action.doubleClick(we);
        //action.perform();
     /*   
    WebElement query=driver.findElement(By.name("userTxt"));
    query.sendKeys("admin");
    query=driver.findElement(By.name("form[USR_PASSWORD]"));
    query=sendKeys("admin");
    query=driver.findElement(By.tagName("button"));
    query.click();
long end = System.currentTimeMillis() + 5000;
        while (System.currentTimeMillis() < end) {
            driver.switchTo().frame("pm-frame-cases");
        }
        query = driver.findElement(By.xpath("//ul[@id='ext-gen37']/div/li/ul/li/div/a/span"));
        query.click();*/
        System.out.println("fin");
	}
}