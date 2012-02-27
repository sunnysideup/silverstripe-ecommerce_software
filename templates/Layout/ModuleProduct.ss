<div id="Sidebar">
	<% include Sidebar_Cart %>
	<% include Sidebar_PreviousAndNextProduct %>
</div>
<div id="Product">

	<h1 class="pageTitle">$Title</h1>
	<% if MetaDescription %><p id="MetaDescriptionHolder">$MetaDescription</p><% end_if %>

	<div class="productDetails">
<% include ProductImage %>
<% include ProductActions %>
	</div>

	<% if ReadMeContent %>
	<h3  class="moduleH3">read me</h3>
	<p>The content of the <a href="$ReadMeURL.URL">README.md</a> has been extracted. You can <a href="#ReadMeHolder" class="md2html" rel="ReadMeHolder">view this converted to html</a>.</p>
	<div id="ReadMeHolder">

		<pre>$ReadMeContent</pre>
		<p class="source">Source: <a href="$ReadMeURL.URL">$ReadMeURL</a></p>
	</div>
	<% end_if %>

	<h3  class="moduleH3">Author(s)</h3>
	<ul id="AuthorList"  class="moduleList">
	<% control Authors %><% if IsAdmin %><% else %>
		<li class="infoItem $FirstLast">
			<strong>
				<% if ListOfModulesLink %><a href="$ListOfModulesLink"><% end_if %>
				<% if ScreenName %>$ScreenName<% else %>$FirstName <% end_if %>
				<% if ListOfModulesLink %></a><% end_if %>
			</strong>
			<% if CompanyName %>(<% if CompanyURL %><a href="$CompanyURL.URL"><% end_if %>$CompanyName<% if CompanyURL %></a><% end_if %>)<% end_if %>
			<% if GithubURL %>, <a href="$GithubURL.URL">Git Hub Profile</a><% end_if %>
			<% if SilverstripeDotOrgURL %>, <a href="$SilverstripeDotOrgURL.URL">Silverstripe.org profile</a><% end_if %>
			<% if AreYouHappyForPeopleToContactYou %>
				<h3  class="moduleH3">Contact Details</h3>
				<ul>
					<% if ContactDetailURL %><li><a href="$ContactDetailURL.URL">Contact details</a></li><% end_if %>
					<% if OtherURL %><li><a href="$OtherURL.URL">More information</a></li><% end_if %>
				</ul>
				<% if AreYouAvailableForPaidSupport %>
					<p>
						<strong><% if Company %><% else %>$FirstName<% end_if %></strong> is available for paid support for this module.
						<ul>
							<% if Rate15Mins %><li>The indicative rate for a fifteen minute call is  $Rate15Mins.Nice ($Currency).</li><% end_if %>
							<% if Rate120Mins %><li>The indicative rate for a two hour support block is $Rate120Mins.Nice ($Currency).</li><% end_if %>
							<% if Rate480Mins %><li>The indicative rate for a full day (eight hours) support block is $Rate480Mins.Nice ($Currency).</li><% end_if %>
						</ul>
					</p>
				<% else %>
					<p>Sorry, <strong><% if Company %><% else %>$FirstName<% end_if %></strong> is not available for paid support for this module.</p>
				<% end_if %>
			<% end_if %>
			</ul>
		</li>
		<% end_if %>
	<% end_control %>
	</ul>

	<h3  class="moduleH3">Links</h3>
	<ul id="ModuleProductLinksList" class="moduleList">
	<% if MainURL %><li class="infoItem"><span class="label">Home page:</span> <span class="value"><a href="$MainURL.URL">$MainURL</a></span></li><% end_if %>
	<% if ReadMeContent %><% else %><% if ReadMeURL %><li class="infoItem"><span class="label">README file:</span> <span class="value"><a href="$ReadMeURL.URL">$ReadMeURL</a></span></li><% end_if %><% end_if %>
	<% if DemoURL %><li class="infoItem"><span class="label">Demo:</span> <span class="value"><a href="$DemoURL.URL">$DemoURL</a></span></li><% end_if %>
	<% if SvnURL %><li class="infoItem"><span class="label">SVN:</span> <span class="value"><a href="$SvnURL.URL">$SvnURL</a></span></li><% end_if %>
	<% if GitURL %><li class="infoItem"><span class="label">GIT:</span> <span class="value"><a href="$GitURL.URL">$GitURL</a></span></li><% end_if %>
	<% if OtherURL %><li class="infoItem"><span class="label">Other repository or downloads:</span> <span class="value"><a href="$OtherURL.URL">$OtherURL</a></span></li><% end_if %>
	</ul>

	<% if Code %><h3 class="moduleH3">Code</h3><ul id="CodeHolderList" class="moduleList"><li class="infoItem first last"><span class="label">Code (folder name):</span> <span class="value">$Code</span></li></ul><% end_if %>

	<% include OtherProductInfo %>

	<% if Form %><div id="FormHolder">$Form</div><% end_if %>


	<% if PageComments %>
		<div id="PageCommentsHolder">
			<h3  class="moduleH3">Comments</h3>
			$PageComments
		</div>
	<% end_if %>


	<% if canEdit %>
	<div id="EmailFormHolder">
	<% if EmailObject %>
		<h2>Emails sent</h2>
		<p>Email has been sent to $EmailObject.To (subject: $EmailObject.Subject).</p>
	<% else %>
		<% if EmailForm %>
			<h2>E-mail Authors</h2>
			$EmailForm
		<% else %>
		<% end_if %>
	<% end_if %>
	</div>
	<% end_if %>


</div>




