<?php

/**
 * Form to edit an existing pod.
 */

declare(strict_types=1);

?>

<form method="get">
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
        <label for="email-input" class="col-2 col-form-label">Registered Email</label>
        <div class="col-10">
            <input type="email" id="email-input" name="email" class="form-control" placeholder="user@domain.com" aria-describedby="email-help">
            <small id="email-help" class="form-text text-muted">Ok to leave blank if you forgot.</small>
        </div>
    </div>

    <br>
    <input type="hidden" name="gettoken">
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
