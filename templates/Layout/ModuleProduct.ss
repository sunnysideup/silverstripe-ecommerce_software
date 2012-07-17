<div id="ModuleProduct">

	<div id="ModuleProductInnerHolder"><% include ModuleProductInner %></div>

	<% if canEmail %>
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


	<% if Form %>
	<div id="FormHolder">$Form</div>
	<% else %>
	<h3  class="moduleH3">Edit this page</h3>
	<p>Please <a href="Security/login/?BackURL=$Link.URLATT">log in</a> to edit this module.</p>
	<% end_if %>

	<% if canEmail %>
	<h2>Edit This Page in CMS</h2>
	<p>If you are an admin then please <a href="/admin/show/$ID">edit this page</a> in the CMS.</p>
	<% end_if %>

	<% if PageComments %>
		<div id="PageCommentsHolder">
			<h3  class="moduleH3">Comments</h3>
			$PageComments
		</div>
	<% end_if %>


</div>

<aside>
	<div id="Sidebar">
		<div class="sidebarTop"></div>
		<% include Sidebar_PreviousAndNextProduct %>
		<% include Sidebar_Cart %>
		<% include Sidebar_UserAccount %>
		<div class="sidebarBottom"></div>
	</div>
</aside>



