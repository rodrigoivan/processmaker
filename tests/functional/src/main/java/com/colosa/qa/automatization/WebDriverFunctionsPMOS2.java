/*
Pensado y Desarrollado por Daniel Canedo
para Colosa QA
http://duhnnie.net
last update: 2012-10-18 Hrs. 11:19
*/

package com.colosa.qa.automatization;

import java.util.List;
import java.util.concurrent.TimeUnit;
import java.lang.Exception;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.interactions.Actions;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.ExpectedCondition;
import com.colosa.qa.automatization.ExtJS.ExtJSGrid;

public class WebDriverFunctionsPMOS2{
	
	private WebDriver driver = null;
	private int skin = 1; //0: classic / 1: uxmodern
	private int timeout = 30; //default timeout in seconds

	public WebDriverFunctionsPMOS2(WebDriver driver, int skin){
		this.driver = driver;
		this.skin = skin;
	}

	public WebDriverFunctionsPMOS2(WebDriver driver, int skin, int timeout){
		this(driver, skin);
		this.timeout = timeout;
	}

	public boolean login(String username, String password, String workspace){
		if(this.skin == 1)
		{
			WebElement we = this.driver.findElement(By.name("form[USR_USERNAME]"));
	        we.sendKeys(username);
	        we = this.driver.findElement(By.name("form[USR_PASSWORD]"));
	        we.sendKeys(password);
	       	we = this.driver.findElement(By.id("workspace"));
	        we.sendKeys(workspace);
	        we.sendKeys(Keys.RETURN);
		}
		else
		{
			WebElement we = this.driver.findElement(By.id("form[USR_USERNAME]"));
	        we.sendKeys(username);
	        we = this.driver.findElement(By.name("form[USR_PASSWORD]"));
	        we.sendKeys(password);
	        we = this.driver.findElement(By.id("form[USER_ENV]"));
	        we.sendKeys(workspace);
	        we = this.driver.findElement(By.id("form[BSUBMIT]"));
	        we.click();
		}
		return true;
	}

	public boolean goSection(String sectionName){

		WebElement we = null;
		List<WebElement> wel;
		this.driver.switchTo().defaultContent();

		if(this.skin == 0)
		{
			this.waitForElementPresent(By.cssSelector("ul#pm_menu li a"),60);
			we = this.driver.findElement(By.id("pm_menu"));
			we = we.findElement(By.linkText(sectionName));
		}
		else
		{
			this.waitForElementPresent(By.xpath("//div[@id='mainTabPanel']"), 60);
			wel = this.driver.findElements(By.xpath("//div[@id='mainTabPanel']/div/div/ul/li"));
			for(WebElement we2:wel)
			{
				we = we2.findElement(By.xpath("a[2]/em/span/span"));
				if(we.getText().equals(sectionName))
					break;
			}
		}
		System.out.println(we); //raro pero se necesita esta linea para que funcione correctamente
		if(we == null)
			return false;
		we.click();
		return true;
	}

	private String[] pathToArray(String path){
		if(path.charAt(0) == '/')
			path = path.substring(1);
		if(path.charAt(path.length()-1) == '/')
			path = path.substring(0, path.length());

		return path.split("/");
	}

	public boolean selectMenuTreePanelOption(String path) throws Exception{
		List<WebElement> wel;
		WebElement option = null;
		String[] pathLevels;
		String aux="";

		if(path.length() == 0)
			return false;
		
		pathLevels = this.pathToArray(path);

		if(pathLevels.length>2)
			throw new Exception("the PATH parameter must contain up to 2 path levels.");

		this.goSection("HOME");
		if(this.skin == 0)
			this.driver.switchTo().frame("casesFrame");
		else
		{
			this.waitForElementPresent(By.id("pm-frame-cases"),this.timeout);
			this.driver.switchTo().frame("pm-frame-cases");
		}

		if(pathLevels.length==2 || path.charAt(path.length()-1)=='/')
			wel = this.driver.findElements(By.xpath("//div[@id='tree-panel']/div/div/ul/div/li"));
		else
			wel = this.driver.findElements(By.xpath("//div[@id='tree-panel']/div/div/ul/div/li/ul/li"));
		for(WebElement we:wel)
			try{
				aux = we.findElement(By.xpath("div/a/span")).getText();
				if(aux.length()>=pathLevels[0].length())
					if(aux.substring(0, pathLevels[0].length()).equals(pathLevels[0]))
					{
						option = we;
						break;
					}
			}catch(java.lang.StringIndexOutOfBoundsException e){
				throw new Exception("No se encontró el grupo de opciones: \""+pathLevels[0]+"\"");
			}
		if(option == null)
			throw new Exception("No se encontró el grupo de opciones: \""+pathLevels[0]+"\"");
		if(pathLevels.length==2)
		{
			wel = option.findElements(By.xpath("ul/li"));
			option = null;
			for(WebElement we:wel)
			{
				aux = we.findElement(By.xpath("div/a/span")).getText();
				try{
					if(aux.substring(0, pathLevels[1].length()).equals(pathLevels[1]))
					{
						option = we;
						break;
					}
				}catch(java.lang.StringIndexOutOfBoundsException e)
				{
					throw new Exception("No se encontró opción: \""+pathLevels[1] + "\" en el grupo de opciones: \""+pathLevels[0]+"\"");
				}
			}
			if(option == null)
				throw new Exception("No se encontró opción: \""+pathLevels[1] + "\" en el grupo de opciones: \""+pathLevels[0]+"\"");
		}
		option.click();
		return true;
	}

	public int startCase(String caseName) throws Exception{
		WebElement we;
		String[] path = this.pathToArray(caseName);
		List<WebElement> wel;
		Actions action = new Actions(this.driver);
		boolean flag = false;
		int value = 0;

		this.selectMenuTreePanelOption("Cases/New case");
		this.driver.switchTo().frame("casesSubFrame");
		//this.waitForElementPresent(By.id("startCaseTreePanel"));//para telefónica
		we = this.driver.findElement(By.id("startCaseTreePanel"));
		if(path.length>2)
			throw new Exception("the string Path parameter can contain up to 2 segments, you asshole!");
		if(path.length==2)
		{
			wel = we.findElements(By.xpath("div/div[2]/ul/div/li"));
			we = null;
			for(WebElement we2:wel)
				if(we2.findElement(By.xpath("div/a/span")).getText().equals(path[0]))
				{
					we = we2;
					break;
				}
			if(we == null)
				return 0;
			wel = we.findElements(By.xpath("ul/li"));
			for(WebElement we2:wel)
			{
				we = we2.findElement(By.xpath("div/a/span"));
				if(we.getText().equals(path[1]))
				{
					flag = true;
					break;
				}
			}
			if(!flag)
				return 0;
		}
		else{
			wel = we.findElements(By.xpath("div/div[2]/ul/div/li/ul/li"));
			for(WebElement we2:wel)
			{
				we = we2.findElement(By.xpath("div/a/span"));
				if(we.getText().equals(path[0]))
				{
					flag = true;
					break;
				}
			}
			if(!flag)
				return 0;
		}
		action.doubleClick(we);
        action.perform();

        value = Integer.parseInt(this.driver.findElement(By.xpath("//div[@id='caseTabPanel']/div[1]/div[1]/ul/li[@id='caseTabPanel__casesTab']")).getText().trim().substring(8));
        return value;
	}

	public int startCase(String caseName, boolean flag) throws Exception{
		int value = this.startCase(caseName);
		if(flag)
		{
			this.waitForElementPresent(By.id("openCaseFrame"), this.timeout);
			this.driver.switchTo().frame("openCaseFrame");
		}
		return value;
	}

	public boolean openProcess(String processName){
		this.goSection("DESIGNER");
		WebElement we = null;
		Actions action = new Actions(this.driver);
		
		this.driver.switchTo().frame("frameMain");
		if(this.skin == 1)
			this.driver.switchTo().frame("pm-frame-processes");
		
		WebElement grid = this.driver.findElement(By.id("processesGrid"));
		ExtJSGrid extGrid = new ExtJSGrid(grid, driver);
		WebElement pager = this.driver.findElement(By.xpath("//div[@id='processesGrid']/div/div[3]/div/table/tbody/tr/td[1]/table/tbody/tr"));
		List<WebElement> wl;
		int index = 1;
		int pages = Integer.parseInt(pager.findElement(By.xpath("td[6]/div")).getText().trim().substring(3));

		while(extGrid.getCurrentPage()<=extGrid.getTotalPages() && we==null){
			System.out.println(extGrid.getCurrentPage() +" de "+ extGrid.getTotalPages());
			wl = grid.findElements(By.xpath("div/div[2]/div/div[1]/div[2]/div/div"));
			for(WebElement we2:wl)
				if(we2.findElement(By.xpath("table/tbody/tr[1]/td[5]/div")).getText().equals(processName))
				{
					we = we2;
					break;
				}
			if(extGrid.getCurrentPage()==extGrid.getTotalPages())
				break;
			extGrid.nextPage();
		}

		if(we==null)
			System.out.println("no se encontr{o el proceso"); //talvez se deberia lanzar un error
		else
		{
			action.doubleClick(we);
        	action.perform();
		}
		return true;
	}

	public boolean openProcess(String processName, String category){
		return true;
	}

	public void selectTaskContextMenuOption(String taskName, String option){
		WebElement designPanel = this.driver.findElement(By.xpath("//div[@id='pm_target']/div[1]/div[1]/div[3]"));
		WebElement task = designPanel.findElement(By.xpath("div[@class='processmap_task___processmaker'][div[1]='"+taskName.trim()+"']"));
		WebElement contextMenu;
		new Actions(this.driver).contextClick(task).perform();
		contextMenu = this.driver.findElement(By.cssSelector("body > div.app_menuRight_container___processmaker"));
		contextMenu.findElement(By.xpath("div[div[2]='"+option.trim()+"']")).click();
	}

    public boolean waitForElementPresent(By elementLocator){
		return this.waitForElementPresent(elementLocator, this.timeout);
	}

	public boolean waitForElementPresent(By elementLocator, long timeout){
        this.driver.manage().timeouts().implicitlyWait(timeout, TimeUnit.SECONDS);
        //try{
            this.driver.findElement(elementLocator);
            return true;
        //}catch(NoSuchElementException e){
            //System.err.print(e.getMessage());
        //}
        //return false;
     }
}