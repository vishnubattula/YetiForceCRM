{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
	<input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
	<input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
	<div class="contents-topscroll">
		<div class="topscroll-div">
			&nbsp;
		</div>
	</div>
	<div class="popupEntriesDiv relatedContents contents-bottomscroll">
		<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="bottomscroll-div">
			<table class="table table-bordered listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">		
						{if $MULTI_SELECT}
							<th class="{$WIDTHTYPE}">
								<input type="checkbox" title="{\App\Language::translate('LBL_SELECT_ALL_CURRENTPAGE')}" class="selectAllInCurrentPage" />
							</th>
						{else}
							<th class="{$WIDTHTYPE}"></th>
							{/if}
							{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<th class="{$WIDTHTYPE}">
								<a href="javascript:void(0);" class="listViewHeaderValues {if $LISTVIEW_HEADER->getFieldName() eq 'listprice' || !$LISTVIEW_HEADER->isListviewSortable()} noSorting {/if}" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->getColumnName()}">{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE_NAME)}
									{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}<img class="sortImage" alt="{\App\Language::translate('LBL_SORT_ASCENDING')}" src="{\App\Layout::getImagePath( $SORT_IMAGE, $MODULE_NAME)}">{else}<img class="hide sortingImage" alt="{\App\Language::translate('LBL_SORT_DESCENDING')}" src="{\App\Layout::getImagePath( 'downArrowSmall.png', $MODULE_NAME)}">{/if}</a>
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="listSearchTd"><button class="btn btn-default" data-trigger="listSearch"><span class="glyphicon glyphicon-search"></span></button></td>
							{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<td>
								{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
								{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$USER_MODEL}
							</td>
						{/foreach}
					</tr>

					{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
						<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}' data-info='{\App\Json::encode($LISTVIEW_ENTRY->getRawData())}'
							{if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if}  id="{$MODULE_NAME}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
							{if $MULTI_SELECT}
								<td class="{$WIDTHTYPE}">
									<input class="entryCheckBox" title="{\App\Language::translate('LBL_SELECT_RECORD')}" type="checkbox" />
								</td>
							{else}
								<td class="{$WIDTHTYPE}"></td>
							{/if}
							{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
								{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
								<td class="listViewEntryValue {$WIDTHTYPE}">
									{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $LISTVIEW_ENTRY->isViewable()}
										<a {if $LISTVIEW_HEADER->isNameField() eq true}class="modCT_{$MODULE}"{/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
											{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
										</a>
									{else if $LISTVIEW_HEADERNAME eq 'listprice'}
										{CurrencyField::convertToUserFormat($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME), null, true, true)}
									{else}
										{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
									{/if}
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>

		<!--added this div for Temporarily -->
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<div class="">
				<div class="emptyRecordsDiv">{\App\Language::translate('LBL_NO_RELATED_RECORDS_FOUND', $MODULE)}.</div>
			</div>
		{/if}
	</div>
{/strip}
