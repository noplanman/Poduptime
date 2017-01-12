<br>
Want your pod listed?<br>
Its easy start monitoring on your pod with a free <a href="https://uptimerobot.com" target="_blank">Uptime Robot</a> account.<br>
Create a monitor for your pod, then in "My Settings" create a monitor-specific API key and paste below.<br>
<br>
<form action="https://<?php echo $_SERVER['HTTP_HOST'] ?>/db/add.php" method="post">
  <label>Monitor API Key: <input type="text" name="url" class="xlarge span8" placeholder="m58978-80abdb799f6ccf15e3e4ttwe"> (don't copy the period on the end)</label><br>
  <label>Pod Domain Name: <input type="text" name="domain" class="xlarge span4" placeholder="domain.com"></label><br>
  <label>Pod Terms Link: <input type="text" name="domain" class="xlarge span4" value="/terms" placeholder="/terms"></label><br>
  <label>Your Email: <input type="text" name="email" class="xlarge span4" placeholder="user@domain.com"></label><br>
  <input type="submit" value="submit">
</form>
