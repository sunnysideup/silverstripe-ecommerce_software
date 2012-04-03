<div id="Sidebar">
	<% include Sidebar_Cart %>
	<% include Sidebar_PreviousAndNextProduct %>
</div>
<div id="Product">

	<div id="ModuleProductInnerHolder"><% include ModuleProductInner %></div>

	<% if Form %>
	<div id="FormHolder">$Form</div>
	<% else %>
	<h3  class="moduleH3">Edit this page</h3>
	<p>Please <a href="Security/login/?BackURL=$Link.URLATT">log in</a> to edit this module.</p>
	<% end_if %>

	<% if PageComments %>
		<div id="PageCommentsHolder">
			<h3  class="moduleH3">Comments</h3>
			$PageComments
		</div>
	<% end_if %>


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


</div>




