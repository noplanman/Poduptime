<br>
Want your pod listed? Or to claim a listed pod?<br>
<br>
<form action="db/add.php" method="get">

  <div class="form-group row">
    <label for="domain-input" class="col-2 col-form-label">Pod Domain *</label>
    <div class="col-10">
      <div class="input-group mb-2 mr-sm-2 mb-sm-0">
        <div class="input-group-addon">https://</div>
        <input type="text" id="domain-input" name="domain" class="form-control" placeholder="domain.com" aria-describedby="domain-help" aria-required="true" required>
      </div>
      <small id="domain-help" class="form-text text-muted">The base domain name of your pod (without trailing slash).</small>
    </div>
  </div>

  <div class="form-group row">
    <label for="email-input" class="col-2 col-form-label">Your Email</label>
    <div class="col-10">
      <input type="email" id="email-input" name="email" class="form-control" placeholder="user@domain.com" aria-describedby="email-help">
      <small id="email-help" class="form-text text-muted">We'll never share your email with anyone else.</small>
    </div>
  </div>

  <div class="form-group">
    <label for="podmin-statement-textarea">Podmin Statement</label>
    <textarea id="podmin-statement-textarea" name="podmin_statement" class="form-control" rows="7" aria-describedby="podmin-statement-help"></textarea>
    <small id="podmin-statement-help" class="form-text text-muted">You can use HTML to include links to your terms and policies and information about your pod you wish to share with users.</small>
  </div>

  <div class="form-check">
    <label class="custom-checkbox">
      <input type="checkbox" name="podmin_notify" class="_form-check-input" aria-describedby="notify-hidden-help" checked>
      <span class="custom-control-indicator"></span>
      <span class="custom-control-description">Notify if pod falls to hidden status</span>
    </label>
    <small id="notify-hidden-help" class="form-text text-muted">You will get a notification if the pod gets hidden due to a bad score.</small>
  </div>

  <br>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>
