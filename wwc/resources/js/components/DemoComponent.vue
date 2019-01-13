<template>
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <div class="card card-default">
          <div class="card-header">Pack Sizes</div>

          <div class="card-body">
            <form action class="form-inline">
              <div v-for="size, index in packSizes" class="mb-2">
                <input type="number" v-model="packSizes[index]" class="form-control" min="1">
                <button class="btn btn-danger" @click.prevent="removePackSize(index)">x</button>
              </div>
              <button class="btn btn-success" @click.prevent="addPackSize">Add a pack size</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-default">
          <div class="card-header">How many?</div>
          <div class="card-body">
            <form @submit.prevent="submit">
              <label>
                Input
                <input type="number" v-model="input" name="input" id="input" min="0">
                <div>
                  <button
                    v-for="size in briefSizes"
                    @click.prevent="changeInput(size)"
                    class="btn"
                  >{{size}}</button>
                </div>
                <input type="submit" value="Calculate" class="btn btn-primary mt-2">
              </label>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-default">
          <div class="card-header">Results</div>
          <div class="card-body">
            <div
              v-if="packSizesError"
              class="alert alert-danger"
            >You need at least 1 pack to work with</div>
            <p v-else-if="results.length === 0">There are no results to show you yet</p>
            <div v-else>
              <span class="total mb-2">Total: {{ total }}</span>
              <ul class="results">
                <li v-for="result, index in results">
                  <p>{{result.quantity}} x {{ result.contains}} = {{ result.total }}</p>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  props: {
    sizes: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      packSizes: this.sizes,
      packSizesError: false,
      input: 0,
      total: 0,
      results: [],
      briefSizes: [1, 250, 251, 501, 12001]
    };
  },
  methods: {
    submit() {
      if (this.packSizes.length === 0) {
        this.packSizesError = true;
      } else {
        this.packSizesError = false;
      }
      const data = {
        packSizes: this.packSizes,
        input: this.input
      };
      axios
        .post("/api/calculate", data)
        .catch(err => console.error(err))
        .then(data => this.handleResults(data.data));
    },
    handleResults(data) {
      this.results = data.packs;
      this.total = data.total;
    },
    changeInput(value) {
      this.input = value;
      this.submit();
    },
    removePackSize(index) {
      this.packSizes.splice(index, 1);
    },
    addPackSize() {
      this.packSizes.push(1);
      this.packSizesError = false;
    }
  }
};
</script>

<style>
.total {
  font-weight: bold;
}
.results {
  list-style-type: none;
  padding-left: 0;
}
</style>

