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

public class dynaform{

	static WebDriver driver;
	public static void main(String[] args) throws Exception{
		//WebDriver driver=FirefoxDriver();
        WebDriver driver= new ChromeDriver();
        WebElement wx = null;
        WebElement element = null;
       WebElement wx1=null;
        WebDriverFunctionsPMOS2 pm = new WebDriverFunctionsPMOS2(driver, 0);
        driver.get("http://192.168.11.132");
        pm.login("admin", "admin", "wsqa");
        pm.openProcess("Form Designer Process - Dynaform");
        element=driver.findElement(By.id("DYNAFORMS"));
        element.click();
        element=driver.findElement(By.id("form[MNU_ADD]"));
        element.click();
        element=driver.findElement(By.id("form[ACCEPT]"));
        element.click();
        element=driver.findElement(By.id("form[DYN_TITLE]"));
        element.sendKeys("Form test number 1");
        element=driver.findElement(By.id("form[SAVE_OPEN]"));
        element.click();
        //titulo del formulario
        element=driver.findElement(By.id("fields_Toolbar"));
        element=element.findElement(By.xpath("tbody/tr/td/table/tbody/tr/td[12]/a/div"));
        element.click();

        element=driver=findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("Dynaform");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("Datos personales");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //crear campos del formulario
        //element.=driver.findElement(By.id("tbody/tr/td/table/tbody/tr/td[5]/a/div"));
        //element.click();
        //campo de texto nombre
        element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[5]/a/div"));
        element.click();
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("nombre");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("nombre");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
//campo de texto apellido
       element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[5]/a/div"));
        element.click();
         element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("apellido");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("apellido");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar campo de texto
        element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[5]/a/div"));
        element.click();
         //crear campo de texto edad
         element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("edad");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("edad");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar el campo email
         element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[5]/a/div"));
        element.click();
        //creacion del campo email
         element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("email");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("email");
        element=driver.findElement(By.id("form[PME_VALIDATE]"));
        element.sendKeys("email");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar area de texto
          element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[10]/a/div"));
        element.click();
        //creacion del campo area de texto
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("observaciones");
        element=driver.findElement(by.id("form[PME_LABEL]"));
        element.sendKeys("observaciones");
        element=driver.findElement(by.id("form[PME_ACCEPT]"));
        element.click();
        //segundo titulo
        element=driver.findElement(By.id("fields_Toolbar"));
        element=element.findElement(By.xpath("tbody/tr/td/table/tbody/tr/td[12]/a/div"));
        element.click();
        element=driver=findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("economics");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("Datos economicos");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar el campo currency
         element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[6]/a/div"));
        element.click();
        //creacion del campo monto
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("monto");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("monto");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar el campo porcentaje
         element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[7]/a/div"));
        element.click();
        //creacion del campo porcenaje entero
        element=driver.findElement("form[PME_XMLNODE_NAME]");
        element.sendKeys("porcentajeEntero");
        element=driver.findElement("form[PME_LABEL]");
        element.sendKeys("porcentaje Entero");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar el campo porcentaje
          element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[7]/a/div"));
        element.click();
        //creacion del campo porcentaje Real
        element=driver.findElement("form[PME_XMLNODE_NAME]");
        element.sendKeys("porcentajeReal");
        element=driver.findElement("form[PME_LABEL]");
        element.sendKeys("porcentaje Real");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar el campo password
         element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[8]/a/div"));
        element.click();
        //creacion del campo password
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("password");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("password");
        element=driver.findElement(by.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar el campo suggest
          element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[9]/a/div"));
        element.click();
        //creacion del campo suggest
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("suggest");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("suggest");
        element=driver.findElement(by.id("form[PME_XMLNODE_VALUE]"));
        element.sendKeys("SELECT USR_UID, USR_USERNAME, CONCAT(USR_FIRSTNAME, " ", USR_LASTNAME) FROM USERS ");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar campo dropdown
         element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[19]/a/div"));
        element.click();
        //cr3acion del campo dropdown
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("dropdown");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("dropdown");
        //agregar elementos al dropdown
        element=driver.findElement(By.id("form[PME_OPTIONS][1][NAME]"));
        element.sendKeys("sistemas");
        element=driver.findElement(By.id("form[PME_OPTIONS][1][LABEL]"));
        element.sendKeys("ingenieria de sistemas");
        element=driver.findElement(By.id("form[PME_OPTIONS][addLink]"));
        element.click();
        element=driver.findElement(By.id("form[PME_OPTIONS][1][NAME]"));
        element.sendKeys("electronica");
        element=driver.findElement(By.id("form[PME_OPTIONS][1][LABEL]"));
        element.sendKeys("ingenieria electronica");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();
        //seleccionar dropdown yes/no
         element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[20]/a/div"));
        element.click();
        //crear campo dropdown yes/no
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("YesNo");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.sendKeys("yes/no");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        elemenc.click();
        //seleccionar campo listbox
           element=driver.findElement(By.id("fields_Toolbar"));
        element=element.find(By.xpath("tbody/tr/td/table/tbody/tr/td[21]/a/div"));
        element.click();
        //crear campo listbox
        element=driver.findElement(By.id("form[PME_XMLNODE_NAME]"));
        element.sendKeys("listbox");
        element=driver.findElement(By.id("form[PME_LABEL]"));
        element.sendKeys("listbox");
        //agregar elementos al listbox
        element=driver.findElement(By.id("form[PME_OPTIONS][1][NAME]"));
        element.sendKeys("text");
        element=driver.findElement(By.id("form[PME_OPTIONS][1][LABEL]"));
        element.sendKeys("text");
        //agregar nuevo elemento
        element=driver.findElement(By.id("form[PME_OPTIONS][addLink]"));
        element.click();
        //agregar nuevo elemento al listbox
        element=driver.findElement(By.id("form[PME_OPTIONS][1][NAME]"));
        element.sendKeys("currency");
        element=driver.findElement(By.id("form[PME_OPTIONS][1][LABEL]"));
        element.sendKeys("currency");
        element=driver.findElement(By.id("form[PME_ACCEPT]"));
        element.click();







   
        //contraseña






       
 		System.out.println("-------------- fin -----------------");
   }
}