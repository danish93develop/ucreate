<div class="loader-wrapper">
    <div class="loader" id="loader-1"></div>
</div>	
<style type="text/css">
a{
  text-decoration: none;
}


.loader-wrapper{
	width: 100%;
    display: inline-block;
    height: 100%;
    position: absolute;
    z-index: 9999;
    background: #fff;
    display: block;
}

.loader-wrapper .loader{
	width: 100px;
    height: 100px;
    border-radius: 100%;
    position: absolute;
    vertical-align: middle;
    top: 45%;
    left: 46%;
}

.main-wrap {
    background: #000;
        text-align: center;
}
.main-wrap h1 {
        color: #fff;
            margin-top: 50px;
    margin-bottom: 100px;
}
.col-md-3 {
	display: block;
	float:left;
	margin: 1% 0 1% 1.6%;
	  background-color: #eee;
  padding: 50px 0;
}

.col:first-of-type {
  margin-left: 0;
}


/* ALL LOADERS */

.loader{
  width: 100px;
  height: 100px;
  border-radius: 100%;
  position: relative;
  margin: 0 auto;
}

/* LOADER 1 */

#loader-1:before, #loader-1:after{
  content: "";
  position: absolute;
  top: -10px;
  left: -10px;
  width: 100%;
  height: 100%;
  border-radius: 100%;
  border: 10px solid transparent;
  border-top-color: #3498db;
}

#loader-1:before{
  z-index: 100;
  animation: spin 1s infinite;
}

#loader-1:after{
  border: 10px solid #ccc;
}

@keyframes spin{
  0%{
    -webkit-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }

  100%{
    -webkit-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}

</style>
