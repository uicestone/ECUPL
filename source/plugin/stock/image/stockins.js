/*
 * Kilofox Services
 * StockIns v9.5
 * Plug-in for Discuz!
 * Last Updated: 2011-08-08
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
function genRandomNum()
{
	r = Math.round(999*Math.random());
	return r;
}
function foxajax()
{
	if ( typeof window.external == 'object' && typeof document.all=='object' )
	{
		r = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else
	{
		r = new XMLHttpRequest();
	}
	return r;
}
// ����Ʊ����
function checkdata(old)
{
	if ( old == 1 )
	{
		var stname = document.form1.stname.value;
		var stnameold = '';
	}
	else
	{
		var stname = document.form1.stname.value;
		var stnameold = document.form1.stnameold.value;
	}
	r = genRandomNum();
	s = 'stname=' + stname + '&stnameold=' + stnameold + '&r=' + r;
	chkd.open('POST','plugin.php?id=stock:index&mod=ajax&section=esnamecheck');
	chkd.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	chkd.onreadystatechange = showresult;
	chkd.send(s);
}
function showresult()
{
	if ( chkd.readyState == 4 )
	{
		dataReturn = chkd.responseText.toString();
		document.getElementById('dispmsg').innerHTML = dataReturn;
	}
}
// �ֺ�
function selectCutType(id)
{
	if ( id == 1 )
	{
		document.getElementById('cut_type1').style.display = 'inline';
		document.getElementById('cut_type2').style.display = 'none';
	}
	else if ( id == 2 )
	{
		document.getElementById('cut_type1').style.display = 'none';
		document.getElementById('cut_type2').style.display = 'inline';
	}
}
function cutCheck1( stockNum, myFunds )
{
	cutNum = document.formcam.cutnum1.value;
	var needFunds = cutNum * parseInt(stockNum/100);
	var msgs = '���ηֺ콫�����ʽ� <span class="xi1">' + needFunds + '</span> Ԫ';
	if ( needFunds > myFunds )
		msgs += '����ֻ�� <span class="s1">' + myFunds + '</span> Ԫ�������Էֺ�';
	document.getElementById('show_msgs').innerHTML = msgs;
}
function cutCheck2( totalNum, myStockNum )
{
	cutNum = document.formcam.cutnum2.value;
	var needNum = cutNum * parseInt(totalNum/100);
	var msgs = '���ηֺ콫���͹�Ʊ <span class="xi1">' + needNum + '</span> ��';
	if ( needNum > myStockNum )
		msgs += '����ֻ�� <span class="s1">' + myStockNum + '</span> �ɣ������Էֺ�';
	document.getElementById('show_msgs').innerHTML = msgs;
}
function melonFormCheck( totalNum, myStockNum, myFunds )
{
	var j = 0;
	for ( i=0; i<document.formcam.cuttype.length; i++ )
	{
		if ( document.formcam.cuttype[i].checked )
			j = i + 1;
	}
	if ( j == 0 )
	{
		msgs = '��ѡ��ֺ췽ʽ��';
		alert(msgs);
		return false;
	}
	else if ( j == 1 )
	{
		var cutNum = document.formcam.cutnum1.value;
		if ( isNaN(cutNum) || cutNum < 1 )
		{
			msgs = '����ȷ��д�����ʽ�';
			alert(msgs);
			return false;
		}
		var needFunds = cutNum * parseInt(totalNum/100);
		if ( needFunds > myFunds )
		{
			msgs = '��ֻ�� ' + myFunds + ' Ԫ�������Էֺ�';
			alert(msgs);
			return false;
		}
	}
	else if ( j == 2 )
	{
		var cutNum = document.formcam.cutnum2.value;
		if ( isNaN(cutNum) || cutNum < 1 )
		{
			msgs = '����ȷ��д�͹�������';
			alert(msgs);
			return false;
		}
		var needNum = cutNum * parseInt(totalNum/100);
		if ( needNum > myStockNum )
		{
			msgs = '��ֻ�� ' + myStockNum + ' �ɣ������Էֺ�';
			alert(msgs);
			return false;
		}
	}
	return true;
}
function changeTwoDecimal(x)
{
	var f_x = parseFloat(x);
	if ( isNaN(f_x) )
	{
		alert('���󣺷����ּ���');
		return false;
	}
	var f_x = Math.round(x*100)/100;
	var s_x = f_x.toString();
	var pos_decimal = s_x.indexOf('.');
	if ( pos_decimal < 0 )
	{
		pos_decimal = s_x.length;
		s_x += '.';
	}
	while ( s_x.length <= pos_decimal + 2 )
	{
		s_x += '0';
	}
	return s_x;
}
// ��ҳʱ����ʾ
function foxTime()
{
	foxdate = new Date();
	year	= foxdate.getFullYear();
	month	= foxdate.getMonth() + 1;
	day		= foxdate.getDate();
	hour	= foxdate.getHours();
	minute	= foxdate.getMinutes();
	second	= foxdate.getSeconds();
	if ( minute < 10 )
	{
		minute = "0" + minute;
	}
	if ( second < 10 )
	{
		second = "0" + second;
	}
	document.getElementById('foxsmtime').innerHTML = year + '��' + month + '��' + day + '�� ' + hour + ':' + minute + ':' + second;
	setTimeout('foxTime()',1000)
}
function isIntNum(str)
{
	if ( str.match(/^[\d]+$/) )
		return true;
	else
		return false;
}
// �����Ʊ�������
function calFeesBuy(tradecharge, stampduty, usercash, numMax)
{
	var price	= document.getElementById('price_buy').value;
	var num		= document.getElementById('num_buy').value;
	var showMsg	= '';
	if ( !price || isNaN(price) || price <= 0 )
		showMsg = '��Ʊ�۸��������';
	else if ( !num || isNaN(num) || num <= 0 )
		showMsg = '��Ʊ�����������';
	else
	{
		if ( num > numMax )
		{
			showMsg = '�����������ܴ��� ' + numMax + ' ��';
		}
		else
		{
			worth		= changeTwoDecimal(price * parseInt(num));
			needFees	= worth * tradecharge / 100;
			needFees	= changeTwoDecimal(needFees >= stampduty ? needFees : stampduty);
			moneyLeft	= changeTwoDecimal(usercash - worth - needFees);
			if ( moneyLeft < 0 )
				showMsg = '�����ʻ���û���㹻���ʽ����������Ʊ��';
			else
				showMsg = '��Ʊ��� <span class="xi1">' + worth + '</span> Ԫ<br/>ӡ��˰�� <span class="xi1">' + needFees + '</span> Ԫ<br/>����ʻ����� <span class="xi1">' + moneyLeft + '</span> Ԫ';
		}
	}
	document.getElementById('feesNeedBuy').innerHTML = showMsg;
}
// ������Ʊ�������
function calFeesSell(tradecharge, stampduty, usercash, numMax)
{
	var price	= document.getElementById('price_sell').value;
	var num		= document.getElementById('num_sell').value;
	var showMsg = '';
	if ( price == '' || isNaN(price) || price <= 0 )
		showMsg = '�����۸��������';
	else if ( !num || isNaN(num) || num <= 0 )
		showMsg = '���������������';
	else
	{
		if ( num > numMax )
		{
			showMsg = '�����������ܴ��� ' + numMax + ' ��';
		}
		else
		{
			worth		= changeTwoDecimal(price * parseInt(num));
			needFees	= worth * tradecharge / 100;
			needFees	= changeTwoDecimal(needFees >= stampduty ? needFees : stampduty);
			moneyLeft	= changeTwoDecimal(usercash - needFees);
			showMsg = 'ӡ��˰�� <span class="xi1">' + needFees + '</span> Ԫ<br/>�����ʻ����� <span class="xi1">' + moneyLeft + '</span> Ԫ';
		}
	}
	document.getElementById('feesNeedSell').innerHTML = showMsg;
}
// ��˾����������ݡ������������Ʊ��������
function showTotalNum(n,base)
{
	if ( isNaN(n) || isNaN(base) )
		result = 0;
	else
		result = n * base;
	document.getElementById('totalnum').innerHTML = result;
}
// ��˾���������������
function applyFormCheck(obj, nameLenMin, nameLenMax, numMin, introLenMax)
{
	if ( obj.stname.value.length < nameLenMin || obj.stname.value.length > nameLenMax )
	{
		alert('��Ʊ���Ƴ��Ȳ���С�� ' + nameLenMin + ' �ֽڻ��ߴ��� ' + nameLenMax + ' �ֽ�');
		obj.stname.focus();
		return false;
	}
	if ( isNaN(obj.stprice.value) || obj.stprice.value < 2 || obj.stprice.value > 99 )
	{
		alert('��Ʊ���е��۲���С�� 2 Ԫ���ߴ��� 99 Ԫ');
		obj.stprice.focus();
		return false;
	}
	if ( isNaN(obj.stnum.value) || obj.stnum.value < 1 )
	{
		alert('��Ʊ������������С�� ' + numMin + ' ��');
		obj.stnum.focus();
		return false;
	}
	if ( obj.comintro.value.length < 10 || obj.comintro.value.length > introLenMax )
	{
		alert('��˾��鳤��������� 10 ������ ' + Math.floor(introLenMax/2) + ' ����֮��');
		obj.comintro.focus();
		return false;
	}
	return true;
}
// ��Ʊ���׼�������
function tradeFormCheck(obj, priceMin, priceMax, numMin, numMax, trade_type)
{
	var price_buy	= document.getElementById('price_buy').value;
	var num_buy		= document.getElementById('num_buy').value;
	var price_sell	= document.getElementById('price_sell').value;
	var num_sell	= document.getElementById('num_sell').value;
	if ( trade_type == 'b' )
	{
		if ( isNaN(price_buy) || price_buy < priceMin || price_buy > priceMax )
		{
			alert('����۸���С�� ' + priceMin + ' Ԫ���ߴ��� ' + priceMax + ' Ԫ');
			obj.price_buy.focus();
			return false;
		}
		if ( isNaN(num_buy) || num_buy < numMin || num_buy > numMax )
		{
			alert('������������С�� ' + numMin + ' �ɻ��ߴ��� ' + numMax + ' ��');
			obj.num_buy.focus();
			return false;
		}
	}
	else if ( trade_type == 's' )
	{
		if ( isNaN(price_sell) || price_sell < priceMin || price_sell > priceMax )
		{
			alert('�����۸���С�� ' + priceMin + ' Ԫ���ߴ��� ' + priceMax + ' Ԫ');
			obj.price_sell.focus();
			return false;
		}
		if ( isNaN(num_sell) || num_sell < numMin || num_sell > numMax )
		{
			alert('������������С�� ' + numMin + ' �ɻ��ߴ��� ' + numMax + ' ��');
			obj.num_sell.focus();
			return false;
		}
	}
	else
	{
		alert('�������ʹ���');
		return false;
	}
	return true;
}