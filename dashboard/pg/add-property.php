      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="m-b-10">Add Property</h5>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="./">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="?page=properties">Properties</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header">
              <!-- <h5>Hello card</h5> -->
            </div>
            <div class="card-body">
              <form action="./ac/action-add-property.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                  <label for="propertyType">Property Type</label>
                  <select class="form-control" id="propertyType" name="propertyType" required>
                    <option value="Apartment">Apartment</option>
                    <option value="Villa">Villa</option>
                    <option value="Home">Home</option>
                    <option value="Office">Office</option>
                    <option value="Building">Building</option>
                    <option value="Townhouse">Townhouse</option>
                    <option value="Shop">Shop</option>
                    <option value="Garage">Garage</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="forSellRent">For Sell / For Rent</label>
                  <select class="form-control" id="forSellRent" name="forSellRent" required>
                    <option value="Sell">For Sell</option>
                    <option value="Rent">For Rent</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="images">Image(s)</label>
                  <input type="file" class="form-control" id="images" name="images[]" multiple required>
                </div>
                <div class="form-group">
                  <label for="ownerPrice">Owner Price</label>
                  <input type="number" class="form-control" id="ownerPrice" name="ownerPrice" required>
                </div>
                <div class="form-group">
                  <label for="listingPrice">Listing Price</label>
                  <input type="number" class="form-control" id="listingPrice" name="listingPrice" required>
                </div>
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="form-group">
                  <label for="address">Address</label>
                  <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div class="form-group">
                  <label for="sqft">Sqft</label>
                  <input type="number" class="form-control" id="sqft" name="sqft" required>
                </div>
                <div class="form-group">
                  <label for="bed">Bed</label>
                  <input type="number" class="form-control" id="bed" name="bed" required>
                </div>
                <div class="form-group">
                  <label for="bath">Bath</label>
                  <input type="number" class="form-control" id="bath" name="bath" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Property</button>
              </form>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->