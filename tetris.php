<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
        <title>Minecraft Tetris - awaCREEPER</title>
    </head>
    <body style="background-color: #FFF">
        <div style="display: inline-box; width:300px; position:absolute">
            <table border="1" style="width: 100%; height: 600px;">
            <tbody id="tb" >
                <?php
                for ($i=0; $i<20; $i++){
                    echo("<tr id='".$i."'>");
                    for ($j=0; $j<10; $j++){
                        echo("<td id='".$i."-".$j."' style='background-size: contain'></td>");
                    }
                    echo("</tr>");
                }
                ?>
            </tbody>
        </table>
        </div>
        <div style="display: inline-box; width:300px; position:absolute; left:330px">
            <table border="1" style="width: 100%; height: 600px;">
            <tbody>
                <tr>
                    <td onclick="tetris.group.rotate(3)">逆时针</td>
                    <td onclick="while(!tetris.group.fixed){tetris.group.move()}">放置</td>
                    <td onclick="tetris.group.rotate(1)">顺时针</td>
                </tr>
                <tr>
                    <td onclick="tetris.group.move(-1,0)">左移</td>
                    <td onclick="tetris.group.move()">下移</td>
                    <td onclick="tetris.group.move(1,0)">右移</td>
                </tr>
                <tr>
                    <td id="score">0</td>
                    <td>得分|用时</td>
                    <td id="time">0</td>
                </tr>
            </tbody>
        </table>
        </div>
        <p>By awaCREEPER (抖音@awacreeper)</p>
        <p>仅供学习参考</p>
        <script type="text/javascript">
        var score = 0;
        var timer = 0;
            function inof(base,arr){
                for (item of arr){
                    if(base == item){
                        return true
                    }
                }
                return false
            }
            tetris = {}; //游戏主对象
            tetris.rotate = function(arr){
                nr = []
                for(i=0; i<arr[0].length; i++){
                    nr.push([])
                    for(j=0; j<arr.length; j++){
                        nr[i].push(arr[j][i])
                    }
                }
                for(item of nr){
                    item.reverse()
                }
                return nr
            }
            var ulmap;
            function e(id){
                return document.getElementById(id)
            }
            tetris.main = document.getElementById('tb'); //游戏容器
            tetris.imgs = [ //游戏贴图
                "stone",
                "sand",
                "chiseled_red_sandstone",
                "chiseled_stone_bricks",
                "cracked_stone_bricks",
                "end_stone_bricks",
                "tnt_side",
                "fire",
                "oak_planks",
                "oak_log",
                "bricks",
                "deepslate_coal_ore",
                "deepslate_copper_ore",
                "deepslate_iron_ore",
                "deepslate_gold_ore",
                "deepslate_diamond_ore",
                "deepslate_emerald_ore",
                "deepslate_lapis_ore",
                "deepslate_redstone_ore",
                ];
            tetris.audiofiles = [
                "explode",
                "fire",
                "stone4",
                "wood4",
                "grass4",
                "sand4",
				"twinkle1"
            ];
            tetris.mati = [
                2, //石头
                3, //木头
                4, //草
                5, //沙子
                1, //火焰
                ]
            tetris.audios = [];
            for(sound of tetris.audiofiles){
                document.body.innerHTML+="<audio id='sound-"+sound+"' preload src='./sounds/"+sound+".ogg'></audio>";
                tetris.audios.push(e("sound-"+sound))
            }
            tetris.xmax = 9; //x限制
            tetris.ymax = 19; //y限制
            tetris.nodes = []; //节点列表
            for(r=0; r<tetris.ymax+1; r++){ //生成行
                tetris.nodes.push([]);
                for(c=0; c<tetris.xmax+1; c++){ //生成列
                    tetris.nodes[r].push({
                        isBlock: false, //是否为实体方块
                        isSuspensible: true, //悬空保留方块
                        row: r, //行数（x）
                        col: c, //列数（y）
                        isSolid: false,
                        isGravity: false,
                        type: null, //类型，用于移动
                        except: false, //是否允许报错（throw）
                        place: function(img){ //放置方法
                            if(!img){
                                img = 0;
                            }
                            e(this.row+"-"+this.col).style.backgroundImage = "url('./imgs/"+tetris.imgs[img]+".png')";
                            this.type = img;
                            this.isSolid = true;
                            this.isBlock = true;
                            if(img == 1){
                                this.isGravity = true
                            }
                            if(img == 7){
                                this.isSolid = false;
                                this.isSuspensible = false
                            }
                        },
                        clear: function(){ //清除方法
                            e(this.row+"-"+this.col).style.backgroundImage = "none";
                            this.type = null;
                            this.isSolid = false;
                            this.isBlock = false;
                            this.isGravity = false;
                            this.isSuspensible = true
                        },
                        move: function(x,y){ //移动方法
                            if(x==undefined){
                                x = 0
                            }
                            if(y==undefined){
                                y = 1
                            }
                            if(this.col+x>tetris.xmax || this.col+x<0 || this.row+y>tetris.ymax || this.row+y<0){
                                if(this.except){
                                    throw Error("Moved out of border (x:"+this.col+x+" y:"+this.row+y+") in tetris.nodes["+this.row+"]["+this.col+"]");
                                }
                                return false
                            }
                            if(!this.isBlock){
                                if(this.except){
                                    throw Error("Moving block is air in tetris.nodes["+this.row+"]["+this.col+"]")
                                }
                                return false
                            }
                            tetris.nodes[this.row+y][this.col+x].place(this.type);
                            if(this.group){
                                tetris.nodes[this.row+y][this.col+x].group = true;
                                this.group = false
                            }
                            this.clear()
                        },
                        group: false //是否在组中
                    })
                }
            }
            tetris.pregroup = {};
            tetris.group = {fixed:true};
            tetris.maps = [
                    [
                        [1,1],
                        [1,1]
                    ],
                    [
                        [0,1],
                        [1,1]
                    ],
                    [
                        [1,0],
                        [1,1]
                    ],
                    [
                        [1,1],
                        [0,1]
                    ],
                    [
                        [1,1],
                        [1,0]
                    ],
                    [
                        [0,0,1],
                        [1,1,1]
                    ],
                    [
                        [1,0,0],
                        [1,1,1]
                    ],
                    [
                        [1,1,1],
                        [1,0,0]
                    ],
                    [
                        [1,1,1],
                        [0,0,1]
                    ],
                    [
                        [1],
                        [1],
                        [1],
                        [1],
                        [1]
                    ],
                    [
                        [1,1,0],
                        [0,1,1]
                    ],
                    [
                        [0,1,1],
                        [1,1,0]
                    ],
                    [
                        [1,1,1,1,1]
                    ],
                    [
                        [1,1,1],
                        [0,1,0]
                    ],
                    [
                        [0,1,0],
                        [1,1,1]
                    ]
                ]
            tetris.bind = function(map,type,mat){
                if(!map){
                    map = tetris.maps[0]
                }
                if(!mat){
                    mat = 0
                }
                
                if(!type){
                    type = 0
                }
                if(inof(type,[8,9])){
                    mat = 1
                }
                if(inof(type,[6])){
                    mat = 2
                }
                if(inof(type,[1])){
                    mat = 3
                }
                if(inof(type,[7])){
                    mat = 4
                }
                repmap = map
                mbox = [
                    [0,0], [0,repmap[0].length - 1],
                    [repmap.length - 1,0], [repmap.length - 1,repmap[repmap.length - 1].length - 1]
                ];
                box = [
                    [0,0], [repmap.length - 1,repmap[repmap.length - 1].length - 1] //[ay,ax],[by,bx]
                ];
                lower = [];
                lin = 0;
                for(item of repmap[repmap.length - 1]){
                    if(item){
                        lower.push([repmap.length - 1,lin]);
                    }else{
                        lower.push(0)
                    }
                    lin++
                }
                left = [];
                lfin = 0;
                for(item of repmap){
                    if(item[0]){
                        left.push([lfin,0]);
                    }else{
                        left.push(0)
                    }
                    lfin++
                }
                right = [];
                rtin = 0;
                for(item of repmap){
                    if(item[item.length-1]){
                        right.push([rtin,item.length-1]);
                    }else{
                        right.push(0)
                    }
                    rtin++
                }
                tetris.pregroup = {
                    map: repmap,
                    maptype: map,
                    type: type,
                    mbox: mbox,
                    box: box,
                    x: 0,
                    y: 0,
                    lower: lower,
                    left: left,
                    right: right,
                    except: false,
                    actived: false,
                    fixed: false,
                    mat: mat,
                    active: function(){
                        this.actived = true;
                        rn = 0;
                        cn = 0;
                        for(r of this.map){
                            for(c of r){
                                if(c){
                                    tetris.nodes[this.y+rn][this.x+cn].place(this.type);
                                    tetris.nodes[this.y+rn][this.x+cn].group = true
                                }
                                cn += 1
                            }
                            rn += 1;
                            cn = 0
                        }
                        tetris.group = this;
                        tetris.group.move = function(x,y){
                            if(!this.fixed){
                                    if(x==undefined){
                                        x=0
                                    }
                                    if(y==undefined){
                                        y=1
                                    }
                                    fixable = false;
                                    if(this.y+this.box[1][0] == 19){
                                        this.fixed = true;
                                        this.diss();
                                        return true
                                    }
                                    for (b of this.lower){
                                        if(b){
                                            if( tetris.nodes[ this.y + b[0] + 1][ this.x + b[1] ].isSolid == true){
                                                fixable = true;
                                            }
                                        }
                                    }
                                    if(fixable){
                                        this.fixed = true;
                                        this.diss();
                                        return true
                                    }
                                    if(tetris.group.x+this.box[0][1]+x>tetris.xmax || tetris.group.x+this.box[0][1]+x<0 || tetris.group.y+this.box[0][0]+y>tetris.ymax || tetris.group.y+this.box[0][0]+y<0 || tetris.group.x+this.box[1][1]+x>tetris.xmax || tetris.group.x+this.box[1][1]+x<0 || tetris.group.y+this.box[1][0]+y>tetris.ymax || tetris.group.y+this.box[1][0]+y<0){
                                        if(this.except){
                                            throw Error("Moved out of border in tetris.group");
                                        }
                                        return false
                                    }
                                    try{
                                        for (b of this.left){
                                            if(b){
                                                if( tetris.nodes[ this.y + b[0]][ this.x + b[1] - 1].isSolid && !tetris.nodes[ this.y + b[0]][ this.x + b[1] - 1].group && x == -1){
                                                    if(this.except){
                                                        throw("Moving to a solid block in tetris.group")
                                                    }
                                                    return false
                                                }
                                            }
                                        }
                                        for (b of this.right){
                                            if(b){
                                                if( tetris.nodes[ this.y + b[0]][ this.x + b[1] + 1].isSolid && !tetris.nodes[ this.y + b[0]][ this.x + b[1] + 1].group && x == 1){
                                                    if(this.except){
                                                        throw("Moving to a solid block in tetris.group")
                                                    }
                                                    return false
                                                }
                                            }
                                        }
                                    }catch(e){
                                        
                                    }
                                    if(y){
                                        switch(y)
                                        {
                                            case -1:
                                                for(r=0; r<this.map.length; r++){
                                                    for(c=0; c<this.map[r].length; c++){
                                                        if(this.map[r][c]){
                                                            tetris.nodes[this.y+r][this.x+c].move(0,-1)
                                                        }
                                                    }
                                                }
                                                this.y -= 1;
                                                return true;
                                                break;
                                            case 1:
                                                for(r=this.map.length-1; r>-1; r--){
                                                    for(c=0; c<this.map[r].length; c++){
                                                        if(this.map[r][c]){
                                                            tetris.nodes[this.y+r][this.x+c].move(0,1)
                                                        }
                                                    }
                                                }
                                                this.y += 1;
                                                return true;
                                                break;
                                        }
                                    }else if(x){
                                        switch(x)
                                        {
                                            case -1:
                                                for(r=0; r<this.map.length; r++){
                                                    for(c=0; c<this.map[r].length; c++){
                                                        if(this.map[r][c]){
                                                            tetris.nodes[this.y+r][this.x+c].move(-1,0)
                                                        }
                                                    }
                                                }
                                                this.x -= 1;
                                                return true;
                                                break;
                                            case 1:
                                                for(r=0; r<this.map.length; r++){
                                                    for(c=this.map[r].length; c>-1; c--){
                                                        if(this.map[r][c]){
                                                            tetris.nodes[this.y+r][this.x+c].move(1,0)
                                                        }
                                                    }
                                                }
                                                this.x += 1;
                                                return true;
                                                break;
                                        }
                                    }else{
                                        if(this.except){
                                            throw Error("Illegal moving arguments in tetris.group")
                                        }
                                        return false
                                    }
                                }else{
                                    if(this.except){
                                        throw("Moving failed because group fixed already")
                                    }
                                    return false
                                }
                            }
                        tetris.group.diss = function(){
                            tetris.audios[tetris.mati[this.mat]].play()
                            for(r=0; r<this.map.length; r++){
                                for(c=0; c<this.map[r].length; c++){
                                    if(this.map[r][c]){
                                        tetris.nodes[this.y+r][this.x+c].group = false;
                                    }
                                }
                            }
                        }
                        tetris.group.rotate = function(time){
                            for(r=0; r<this.map.length; r++){
                                for(c=0; c<this.map[r].length; c++){
                                    if(this.map[r][c]){
                                        tetris.nodes[this.y+r][this.x+c].clear();
                                    }
                                }
                            }
                            if(!time){
                                time = 0;
                            }
                            ulmap = tetris.group.map;
                            wt=0;
                            while (wt<time){
                                ulmap = tetris.rotate(ulmap);
                                wt+=1
                            }
                            tetris.bind(ulmap,tetris.group.type);
                            tetris.pregroup.x = tetris.group.x;
                            tetris.pregroup.y = tetris.group.y;
                            tetris.pregroup.active()
                        }
                        return tetris.pregroup
                        }
                    }
                }
                tetris.heartbeat = function(){
                    timer += 0.1;
                    e("time").innerHTML = parseInt(timer * 10) / 10;
                    if(tetris.group.fixed){
                        tetris.bind(
                            tetris.maps[parseInt(Math.random() * tetris.maps.length)],
                            parseInt(Math.random()*tetris.imgs.length)
                        );
                        tetris.pregroup.x = ((tetris.xmax + 1) / 2) - 2;
                        tetris.pregroup.active()
                    }
                    rn = 0;
                    for (r of tetris.nodes) {
                        rowfulled = true;
                        for(c of r){
                            if(!c.group){
                                if(c.row != 19){
                                    if( c.isGravity==true && !tetris.nodes[c.row + 1][c.col].isSolid ){
                                        c.move()
                                    }
                                    if( c.isSuspensible==false && !tetris.nodes[c.row + 1][c.col].isSolid ){
                                        c.clear()
                                    }
                                }
                                if(!c.isBlock || c.group){
                                    rowfulled = false;
                                }
                                if( c.type  == 6 && tetris.nodes[c.row - 1][c.col].type == 7 && !tetris.nodes[c.row - 1][c.col].group){
                                    tetris.clearrow(c.row);
                                    tetris.audios[0].play()
                                }
                            }
                        }
                        if(rowfulled){
                            for(c of r){
                                c.clear()
                            }
                            for(rf = rn - 1; rf > -1; rf--){
                                for(c of tetris.nodes[rf]){
                                    if(!c.group){
                                        c.move()
                                    }
                                }
                            }
                            score+=5
                        }
                        rn += 1
                    }
                    e("score").innerHTML = score;
                }
                tetris.movehb = function(){
                    tetris.group.move()
                }
                document.body.onkeydown = function(){
                    k = window.event.keyCode;
                    if(k == 37 || k == 65){
                        tetris.group.move(-1,0)
                    }
                    if(k == 39 || k == 68){
                        tetris.group.move(1,0)
                    }
                    if(k == 40 || k == 83){
                        tetris.group.move()
                    }
                    if(k == 69){
                        tetris.group.rotate(1)
                    }
                    if(k == 81 || k == 38){
                        tetris.group.rotate(3)
                    }
                    if(k == 90 || k == 88){
                        while(!tetris.group.fixed){
                            tetris.group.move()
                        }
                    }
                }
                tetris.clearrow = function(row){
                    for(c of tetris.nodes[row]){
                        c.clear()
                    }
                    rn = row;
                    for(rf = rn - 1; rf > -1; rf--){
                        for(c of tetris.nodes[rf]){
                            if(!c.group){
                                c.move()
                            }
                        }
                    }
                    score+=8;
					tetris.audios[7].play();
                }
                tetris.updater = setInterval(tetris.heartbeat,100);
                tetris.moveupdater = setInterval(tetris.movehb,1000)
        </script>
    </body>
</html>