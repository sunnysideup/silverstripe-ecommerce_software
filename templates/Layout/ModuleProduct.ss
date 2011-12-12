<div id="Sidebar">
	<% include Sidebar_Cart %>
	<% include Sidebar_Products %>
</div>
<div id="Product">

	<h1 class="pageTitle">$Title</h1>
	<% if MetaDescription %><p id="MetaDescriptionHolder">$MetaDescription</p><% end_if %>

	<div class="productDetails">
		<div class="productImage">
<% if Image.ContentImage %>
			<img class="realImage" src="$Image.ContentImage.URL" alt="<% sprintf(_t("Product.IMAGE","%s image"),$Title) %>" />
<% end_if %>
		</div>
<% include ProductActions %>
	</div>

	<% if Code %><h3>Main Details</h3><ul id="CodeHolderList" class="moduleList"><li class="infoItem first last"><span class="label">Code (folder name):</span> <span class="value">$Code</span></li></ul><% end_if %>

	<h3>Description</h3>
	<% if Content %><div id="ContentHolder">$Content</div><% end_if %>

	<h3>Author(s)</h3>
	<ul id="AuthorList"  class="moduleList">
	<% control Authors %>
		<li class="infoItem $FirstLast">
			<strong>
				<% if ScreenName %>$ScreenName<% else %>$FirstName <% end_if %>
			</strong>
			<% if CompanyName %>(<% if CompanyURL %><a href="$CompanyURL.URL"><% end_if %>$CompanyName<% if CompanyURL %></a><% end_if %>)<% end_if %>
			<% if GithubURL %>, <a href="$GithubURL.URL">Git Hub Profile</a><% end_if %>
			<% if SilverstripeDotOrgURL %>, <a href="$SilverstripeDotOrgURL.URL">Silverstripe.org profile</a><% end_if %>

			<% if AreYouHappyForPeopleToContactYou %>
				<h3>Contact Details</h3>
				<ul>
					<% if ContactDetailURL %><li><a href="$ContactDetailURL.URL">Contact details</a></li><% end_if %>
					<% if OtherURL %><li><a href="$OtherURL.URL">More information</a></li><% end_if %>
				</ul>
				<% if AreYouHappyForPeopleToContactYou %>
					<p><strong><% if Company %><% else %>$FirstName<% end_if %></strong> is available for paid support.</p>
				<% else %>
					<p>Sorry, <strong><% if Company %><% else %>$FirstName<% end_if %></strong> is not available for paid support.</p>
				<% end_if %>
			<% end_if %>
			</ul>
		</li>
	<% end_control %>
	</ul>

	<h3>Links</h3>
	<ul id="ModuleProductLinksList" class="moduleList">
	<% if MainURL %><li class="infoItem"><span class="label">Home page:</span> <span class="value"><a href="$MainURL.URL">$MainURL</a></span></li><% end_if %>
	<% if ReadMeURL %><li class="infoItem"><span class="label">READ ME file:</span> <span class="value"><a href="$ReadMeURL.URL">$ReadMeURL</a></span></li><% end_if %>
	<% if DemoURL %><li class="infoItem"><span class="label">Demo:</span> <span class="value"><a href="$DemoURL.URL">$DemoURL</a></span></li><% end_if %>
	<% if SvnURL %><li class="infoItem"><span class="label">SVN:</span> <span class="value"><a href="$SvnURL.URL">$SvnURL</a></span></li><% end_if %>
	<% if GitURL %><li class="infoItem"><span class="label">GIT:</span> <span class="value"><a href="$GitURL.URL">$GitURL</a></span></li><% end_if %>
	<% if OtherURL %><li class="infoItem"><span class="label">Other repository or downloads:</span> <span class="value"><a href="$OtherURL.URL">$OtherURL</a></span></li><% end_if %>
	</ul>

	<% include OtherProductInfo %>

	<% if Form %><div id="FormHolder">$Form</div><% end_if %>

	<% if PageComments %><div id="PageCommentsHolder">$PageComments</div><% end_if %>

</div>




