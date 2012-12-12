package com.colosa.qa.automatization.tests.salesProcess;

import org.junit.Assert;
import org.junit.AfterClass;
import org.junit.Test;

import com.colosa.qa.automatization.pages.*;
import com.colosa.qa.automatization.common.*;
import org.openqa.selenium.WebElement;

import java.io.FileNotFoundException;
import java.io.IOException;

public class TestNewHireRequest{

	protected static int caseNum;
	protected static String approve = "Yes";
	protected static String approve2 = "Yes";
	@Test
	public void runProcess() throws FileNotFoundException, IOException, Exception{
		Pages.Login().gotoUrl();
		Pages.Login().loginUser("hector", "sample", "workflow");
		Pages.Main().goHome();	
		caseNum = Pages.Home().startCase("New Hire Request v_1 (Request for new hire)");

		FormFieldData[] arrayData = new FormFieldData[8];
		arrayData[0] = new FormFieldData();
		arrayData[1] = new FormFieldData();
		arrayData[2] = new FormFieldData();
		arrayData[3] = new FormFieldData();
		arrayData[4] = new FormFieldData();
		arrayData[5] = new FormFieldData();
		arrayData[6] = new FormFieldData();
		arrayData[7] = new FormFieldData();

		arrayData[0].fieldPath = "form[reqPropDate]";
		arrayData[0].fieldFindType = "id";
		arrayData[0].fieldType = "textbox";
		arrayData[0].fieldValue = "2012-12-12";
		arrayData[1].fieldPath = "form[reqPosition]";
		arrayData[1].fieldFindType = "id";
		arrayData[1].fieldType = "textbox";
		arrayData[1].fieldValue = "Prueba...";
		arrayData[2].fieldPath = "form[reqSalary]";
		arrayData[2].fieldFindType = "id";
		arrayData[2].fieldType = "textbox";
		arrayData[2].fieldValue = "23564";
		arrayData[3].fieldPath = "form[reqApplicant]";
		arrayData[3].fieldFindType = "id";
		arrayData[3].fieldType = "textbox";
		arrayData[3].fieldValue = "3";
		arrayData[4].fieldPath = "form[reqJDesc]";
		arrayData[4].fieldFindType = "id";
		arrayData[4].fieldType = "textarea";
		arrayData[4].fieldValue = "Prueba...";
		arrayData[5].fieldPath = "form[reqJQual]";
		arrayData[5].fieldFindType = "id";
		arrayData[5].fieldType = "textarea";
		arrayData[5].fieldValue = "Prueba...";
		arrayData[6].fieldPath = "form[reqComments]";
		arrayData[6].fieldFindType = "id";
		arrayData[6].fieldType = "textarea";
		arrayData[6].fieldValue = "Prueba...";
		arrayData[7].fieldPath = "form[Submit]";
		arrayData[7].fieldFindType = "id";
		arrayData[7].fieldType = "button";
		arrayData[7].fieldValue = "";
		Pages.InputDocProcess().openCaseFrame();
		Assert.assertTrue(FormFiller.formFillElements(arrayData));
		Assert.assertTrue(Pages.InputDocProcess().continuebtn());
		Pages.Main().logout();

	}

	@Test
	public void reviewRequest() throws FileNotFoundException, IOException, Exception{
		Pages.Login().gotoUrl();
		Pages.Login().loginUser("pablo", "sample", "");
		Pages.Main().goHome();
		Pages.Home().openCase(caseNum);
		FormFieldData[] arrayData = new FormFieldData[2];
		arrayData[0] = new FormFieldData();
		arrayData[1] = new FormFieldData();

		arrayData[0].fieldPath = "form[apRequest]";
		arrayData[0].fieldFindType = "id";
		arrayData[0].fieldType = "dropdown";
		arrayData[0].fieldValue = approve;
		arrayData[1].fieldPath = "form[Submit]";
		arrayData[1].fieldFindType = "id";
		arrayData[1].fieldType = "button";
		arrayData[1].fieldValue = "";
		Pages.InputDocProcess().openCaseFrame();
		Assert.assertTrue(FormFiller.formFillElements(arrayData));
		Assert.assertTrue(Pages.InputDocProcess().continuebtn());
		Pages.Main().logout();
	}
	@Test
	public void budgetApproval() throws FileNotFoundException, IOException, Exception{

		Pages.Login().gotoUrl();
		Pages.Login().loginUser("iver", "sample", "");
		Pages.Main().goHome();
		Pages.Home().openCase(caseNum);

		FormFieldData[] arrayData = new FormFieldData[3];
		arrayData[0] = new FormFieldData();
		arrayData[1] = new FormFieldData();
		arrayData[2] = new FormFieldData();

		arrayData[0].fieldPath = "form[bgRequest]";
		arrayData[0].fieldFindType = "id";
		arrayData[0].fieldType = "dropdown";
		arrayData[0].fieldValue = approve2;
		arrayData[1].fieldPath = "form[bgSalary]";
		arrayData[1].fieldFindType = "id";
		arrayData[1].fieldType = "textbox";
		arrayData[1].fieldValue = "2456";
		arrayData[2].fieldPath = "form[BGSubmit]";
		arrayData[2].fieldFindType = "id";
		arrayData[2].fieldType = "button";
		arrayData[2].fieldValue = "";
		Pages.InputDocProcess().openCaseFrame();
		Assert.assertTrue(FormFiller.formFillElements(arrayData));
		Assert.assertTrue(Pages.InputDocProcess().continuebtn());
		Pages.Main().logout();

	}

	@Test
	public void positionPublication() throws FileNotFoundException, IOException, Exception{

		Pages.Login().gotoUrl();
		Pages.Login().loginUser("pablo", "sample", "");
		Pages.Main().goHome();
		Pages.Home().openCase(caseNum);

		FormFieldData[] arrayData = new FormFieldData[5];
		arrayData[0] = new FormFieldData();
		arrayData[1] = new FormFieldData();
		arrayData[2] = new FormFieldData();
		arrayData[3] = new FormFieldData();
		arrayData[4] = new FormFieldData();

		arrayData[0].fieldPath = "form[propAdvertising][2]";
		arrayData[0].fieldFindType = "id";
		arrayData[0].fieldType = "check";
		arrayData[0].fieldValue = "";
		arrayData[1].fieldPath = "form[propAdvertising][3]";
		arrayData[1].fieldFindType = "id";
		arrayData[1].fieldType = "check";
		arrayData[1].fieldValue = "";
		arrayData[2].fieldPath = "form[Position_ID]";
		arrayData[2].fieldFindType = "id";
		arrayData[2].fieldType = "textbox";
		arrayData[2].fieldValue = "AB-123";
		arrayData[3].fieldPath = "form[propStartDate]";
		arrayData[3].fieldFindType = "id";
		arrayData[3].fieldType = "textbox";
		arrayData[3].fieldValue = "2012-12-29";
		arrayData[4].fieldPath = "form[Submit]";
		arrayData[4].fieldFindType = "id";
		arrayData[4].fieldType = "button";
		arrayData[4].fieldValue = "";

		FormFieldData[] arrayData2 = new FormFieldData[1];
		arrayData2[0] = new FormFieldData();

		arrayData2[0].fieldPath = "form[submit]";
		arrayData2[0].fieldFindType = "id";
		arrayData2[0].fieldType = "button";
		arrayData2[0].fieldValue = "";

		Pages.InputDocProcess().openCaseFrame();
		Assert.assertTrue(FormFiller.formFillElements(arrayData));
		Assert.assertTrue(FormFiller.formFillElements(arrayData2));
		Assert.assertTrue(Pages.InputDocProcess().continuebtn());		
	}

	@Test
	public void endPositionPublication() throws FileNotFoundException, IOException, Exception{

		Pages.Home().openCase(caseNum);

		FormFieldData[] arrayData = new FormFieldData[3];
		arrayData[0] = new FormFieldData();
		arrayData[1] = new FormFieldData();
		arrayData[2] = new FormFieldData();
		
		arrayData[0].fieldPath = "form[propEndDate]";
		arrayData[0].fieldFindType = "id";
		arrayData[0].fieldType = "textbox";
		arrayData[0].fieldValue = "2013-12-29";
		arrayData[1].fieldPath = "form[propStatus]";
		arrayData[1].fieldFindType = "id";
		arrayData[1].fieldType = "dropdown";
		arrayData[1].fieldValue = "Open";
		arrayData[2].fieldPath = "form[Submit]";
		arrayData[2].fieldFindType = "id";
		arrayData[2].fieldType = "button";
		arrayData[2].fieldValue = "";

		Pages.InputDocProcess().openCaseFrame();
		Assert.assertTrue(FormFiller.formFillElements(arrayData));
		Assert.assertTrue(Pages.InputDocProcess().continuebtn());

	}
}