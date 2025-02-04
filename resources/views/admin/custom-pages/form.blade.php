            @csrf
            <div class="row g-3 form-outline mb-3">
                <label for="validationPathname" class="form-label">Pathname</label>
                <div class="input-group">
                    <span class="input-group-text" id="pathname-prefix">https://mensa.org.hk/</span>
                    <input type="text" name="pathname" class="form-control" id="validationPathname"
                        maxlength="768" pattern="[A-Za-z0-9-\/]+" placeholder="abc/xyz-123" aria-describedby="pathname-prefix"
                        value="{{ old('pathname') }}" required />
                    <div id="pathnameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationTitle" class="form-label">Title</label>
                <input type="text" name="title" class="form-control" id="validationTitle"
                    maxlength="43" placeholder="title" value="{{ old('title') }}" required />
                <div id="titleFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationOGImageUrl" class="form-label">Open Graph Image URL</label>
                <input type="url" name="og_image_url" class="form-control" id="validationOGImageUrl"
                    maxlength="65535" placeholder="https://google.com" value="{{ old('og_image_url') }}" />
                <div id="OGImageUrlFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationDescription" class="form-label">Description</label>
                <input type="text" name="description" class="form-control" id="validationDescription"
                    maxlength="65" placeholder="https://google.com" value="{{ old('description') }}" required />
                <div id="descriptionFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationContent" class="form-label">content</label>
                <textarea name="content" id="validationContent" maxlength="65535" required>
                    {!! old('content') !!}
                </textarea>
                <div id="contentFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
