// header-component.js
Vue.component("app-header", {
  data() {
    return {
      user: JSON.parse(localStorage.getItem("user")) || null,
    };
  },
  mounted() {
    // // 获取.top_header 元素
    // var header = document.querySelector('.top_header');
    // window.addEventListener('scroll', function() {
    //     if (window.scrollY >= 100) {
    //       // 修改背景颜色
    //         header.style.backgroundColor ='#F2F2F2';
    //     } else {
    //       // 修改背景颜色
    //         header.style.backgroundColor ='white';
    //     }
    //   });
  },
  methods: {
    managedata() {
      // 刷新页面
      window.location.href = "manage_data.html";
    },
    userdata() {
      // 刷新页面
      window.location.href = "manage_user.html";
    },
    logout() {
      // 清除 localStorage 中的用户信息
      localStorage.removeItem("user");
      // 刷新页面
      // window.location.reload();
      window.location.href = "index.html";
    },
  },
  template: `
    <div class="top_header">
        <div class="header_center">
            <div style="height:100px; display:flex; justify-content: space-between; width: 100%;">
                <div class="logo">
                    <a href="index.html"> </a>
                </div>
                <div class="nav">
                    <ul>
                      
                        <li><a href="study_dataset.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">RESOURCES</a></li>
						<li class="key1">
						    <a href="browse_circRNA.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">CIRCRNAS</a>
						</li>
                        <li><a href="expression.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">EXPRESSIONS</a></li>
                        <li><a href="pipeline.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">PIPELINE</a></li>
                        <li><a href="help.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">HELP</a></li>
						
                    </ul>
                </div>
                <div class="nav_right">
                    <div class="xiala">
                        <!-- 替换图标为图片 -->
                        <img src="/images/dropdown.png" alt="展开" class="toggle-icon" style="width: 50px; height: 50px;">
                        <div class="xiala_nav">
                            <ul>
                            
                                <li><a href="study_dataset.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">RESOURCES</a></li>
                                <li class="key1">
                                    <a href="browse_circRNA.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">CIRCRNAS</a>
                                </li>
                                <li><a href="expression.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">EXPRESSIONS</a></li>
                                <li><a href="pipeline.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">PIPELINE</a></li>
                                <li><a href="help.html" style="font-family:'Roboto';font-weight:700; text-transform: uppercase;">HELP</a></li>
                                
                            </ul>
                        </div>
                    </div>
                    <span v-if="!user"><a href="login.html" style="font-family:'Roboto';">Log in</a></span>
                    <el-dropdown v-else>
                        <span class="el-dropdown-link">
                            <i class="el-icon-user" style="font-size:30px;color: #F5F7FA;font-weight:700;"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item @click.native="managedata" style="font-family:'Roboto';font-weight:700;">Manage</el-dropdown-item>
                            
                            <el-dropdown-item @click.native="logout" style="font-family:'Roboto';font-weight:700;">Log out</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </div>
        </div>
    </div>
	
    `,
});

{
  /* <span><a href="contact.html">Contact us</a></span>
                <span class="separator">|</span> */
}
// <a id="search_btn" href="search.html" style="padding-left: 10px;"><img src="images/search.svg"></a>
// <span style="font-size:20px;">Welcome,{{ user.email }}!</span>
// <li><a href="differential_expression.html">Differential Expression</a></li>
