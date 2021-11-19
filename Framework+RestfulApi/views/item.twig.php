{% extends "base.twig.php" %}

{%block body%}
<h1>Task #{{item.index}}</h1>
<h5>{{item.name}}</h5>
<div>{{item.task}}</div>
<div>{{item.status}}</div>

<a href="/">Back home</a>
	
{%endblock%}

