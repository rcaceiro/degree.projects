const app = require('http').createServer(function (req, res)
                                         {
                                          // Set CORS headers
                                          res.setHeader('Access-Control-Allow-Origin', '*'); //
                                          res.setHeader('Access-Control-Request-Method', '*');
                                          res.setHeader('Access-Control-Allow-Methods', 'OPTIONS, GET');
                                          res.setHeader('Access-Control-Allow-Credentials', true);
                                          res.setHeader('Access-Control-Allow-Headers', req.header.origin);
                                          if (req.method === 'OPTIONS')
                                          {
                                           res.writeHead(200);
                                           res.end();
                                          }
                                         });

const io = require('socket.io')(app);
const axios = require('axios');
const globals = require('./_globals');
const GameList = require('./gamelist');

app.listen(8080, function ()
{
 console.log('listening on localhost:8080');
});


let users = new Map();
let games = new GameList();
let lobbyMessages = [];
let activeGamesMessages = new Map();

// middleware
io.use((socket, next) =>
       {
        let token = socket.handshake.query.token;

        if (token && token.length > 10)
        {
         axios.create({
                       headers: {
                        Accept: 'application/json',
                        Authorization: 'Bearer ' + token
                       }
                      })
              .get(globals.SERVER_URL + '/api/details')
              .then(response =>
                    {
                     //SAVE USER NICKNAME AND TOKEN
                     let user = {};
                     user.nickname = response.data.data.nickname;
                     user.token = token;
                     users.set(socket.id, user);

                     console.log('\nAuthenticated!\nToken: ',
                                 token.substring(token.length - 10, token.length));

                     return next();
                    })
              .catch(() =>
                     {
                      console.log('\nAuthentication error\nToken: ',
                                  token.substring(token.length - 10, token.length));
                      return next(new Error('Authentication error'));
                     });
        }
        else
        {
         console.log('\nAuthentication error\nNo token!!');
         return next(new Error('Authentication error'));
        }
       });


//USERS SEND TOKEN AND NICKNAME
io.on('connection', (socket) =>
{
 let user = users.get(socket.id);
 console.log(user.nickname + ' has connected');
 socket.emit('connect');
 //REJOIN ACTIVE GAMES
 let gamesToReJoin = games.getConnectedGamesOf(user.nickname);
 for (let game of gamesToReJoin)
 {
  console.log(user.nickname + ' rejoined game ' + game.gameID);
  socket.join(game.gameID);
 }

 socket.on('disconnect', function ()
 {
  console.log(user.nickname + ' has disconnected');
 });

 /****************************************
  *                                      *
  *                CHAT                  *
  *                                      *
  ****************************************/

 socket.on('get_lobby_chat', () =>
 {
  let lastChatEntries = []; //10
  for (let i = 0; i < lobbyMessages.length && i < 10; ++i)
  {
   lastChatEntries.push(lobbyMessages[i]);
  }
  socket.emit('set_lobby_messages', lastChatEntries);
 });

 socket.on('send_lobby_message', (data) =>
 {
  if (!data || typeof data.username === 'undefined' ||
      typeof data.text === 'undefined')
  {
   return;
  }
  data.time = Date.now() / 1000;
  lobbyMessages.push(data);
  io.emit('add_lobby_message', data);
 });

 socket.on('get_active_game_chat', (data) =>
 {
  if (!data || isNaN(data.gameID))
  {
   return;
  }

  let lastChatEntries = []; //10
  let allChatEntries = activeGamesMessages.get(data.gameID);
  for (let i = 0; i < allChatEntries.length && i < 10; ++i)
  {
   lastChatEntries.push(allChatEntries[i]);
  }
  socket.emit('set_active_game_messages', {
   chatEntries: lastChatEntries,
   gameID: data.gameID
  });
 });

 socket.on('send_active_game_message', (data) =>
 {
  if (!data || typeof data.username === 'undefined' ||
      typeof data.text === 'undefined' || isNaN(data.gameID))
  {
   return;
  }
  let messageObj = {};
  messageObj.time = Date.now() / 1000;
  messageObj.username = data.username;
  messageObj.text = data.text;

  activeGamesMessages.get(data.gameID).push(messageObj);
  io.to(data.gameID).emit('add_active_game_message', {
   gameID: data.gameID,
   message: messageObj
  });
 });


 /****************************************
  *                                      *
  *             LOBBY                    *
  *                                      *
  ****************************************/
 socket.on('get_lobby', function ()
 {
  console.log(user.nickname + ' requested full lobby');
  socket.emit('set_lobby', toDTOs(games.getLobbyGamesOf(user.nickname)));
 });

 socket.on('create_game', function (data)
 {
  if (!data)
  {
   data = {
    gameRows: 0,
    gameColumns: 0,
    multiPlayer: true
   };
  }
  if (typeof data.gameRows === 'undefined' || isNaN(data.gameRows) || data.gameRows > 8)
  {
   data.gameRows = 0;
  }
  if (typeof data.gameColumns === 'undefined' || isNaN(data.gameColumns) || data.gameColumns > 8)
  {
   data.gameColumns = 0;
  }
  if (typeof data.multiPlayer === 'undefined')
  {
   data.multiPlayer = true;
  }
  if (data.gameRows % 2 === 1 && data.gameColumns % 2 === 1)
  {
   console.log(user.nickname + ' attempted to create a game ' +
               'with an invalid configuration');
   return;
  }
  if (data.multiPlayer === true)
  {
   console.log(user.nickname + ' created a new multi player game');
   let game = toDTO(games.createGame(user.nickname,
                                     user.token,
                                     data.gameRows,
                                     data.gameColumns,
                                     4,
                                     gameStartedCallBack,
                                     turnEndedCallBack,
                                     gameEndedCallBack,
                                     turnPieceCallback));
   //Create chat for game
   activeGamesMessages.set(game.gameID, []);
   //Create channel for game
   socket.join(game.gameID);
   //Add active game to the client that created the game
   socket.emit('add_active_game', game);
   //Add lobby game to all clients except user that created it
   socket.broadcast.emit('add_lobby_game', game);
  }
  else
  {
   //SINGLE PLAYER
   console.log(user.nickname + ' created a new single player game');
   let game = toDTO(games.createGame(user.nickname,
                                     user.token,
                                     data.gameRows,
                                     data.gameColumns,
                                     1,
                                     gameStartedCallBack,
                                     turnEndedCallBack,
                                     gameEndedCallBack,
                                     turnPieceCallback));
   //Create chat for game
   activeGamesMessages.set(game.gameID, []);
   //Create channel for game
   socket.join(game.gameID);
   //Add active game to the client that created the game
   socket.emit('add_active_game', game);
   //GAME STARTS AUTOMATICALLY
  }
 });

 socket.on('join_game', function (data)
 {
  if (!data || !data.gameID || isNaN(data.gameID))
  {
   return;
  }
  //JOIN GAME WITH GAMESTARTED CALLBACK;
  let game = games.joinGame(data.gameID, {
                             'nickname': user.nickname,
                             'bot': false
                            }
  );
  if (game !== null)
  {
   game = toDTO(game);
   //Add user to game channel
   socket.join(game.gameID);
   //Add active game to the client that joined the game
   socket.emit('add_active_game', game);
   //Remove clients lobby game
   socket.emit('remove_lobby_game', game.gameID);

   if (!game.gameStarted)
   {
    console.log(user.nickname + ' joined game ' + game.gameID);
    //Update lobby game to all clients
    socket.broadcast.emit('update_lobby_game_players', game);
    //Update Active game to all clients
    io.to(game.gameID).emit('update_active_game', game);
   }
  }
  else
  {
   //Error joining
   console.log('Error! ' + user.nickname + ' cannot join game ' + data.gameID);
  }
 });

 socket.on('add_bot', function (data)
 {
  if (!data || !data.gameID || isNaN(data.gameID))
  {
   return;
  }
  let namesArray = ['Mechan', 'Uzoxroid', 'Ibooid',
                    'Erux', 'Fiber', 'Intron', 'Ajasx', 'Ugiv',
                    'Tracker', 'Isek', 'Mechi', 'Wall-E', 'Maiden',
                    'C3-PO', 'RD-D2', 'BB-8'];

  const randName = namesArray[Math.floor(Math.random() * namesArray.length)];
  let robotName = 'Robot-' + randName;
  let game = games.joinGame(data.gameID, {
   'nickname': robotName,
   'bot': true
  });
  if (game !== null)
  {
   game = toDTO(game);
   if (!game.gameStarted)
   {
    console.log(robotName + ' joined game ' + game.gameID);
    //Update lobby game to all clients
    socket.broadcast.emit('update_lobby_game_players', game);
    //Update Active game to all clients
    io.to(game.gameID).emit('update_active_game', game);
   }
  }
  else
  {
   //Error joining
   console.log('Error! ' + robotName + ' cannot join game ' + data.gameID);
  }
 });

 /****************************************
  *                                      *
  *         ACTIVE GAMES                 *
  *                                      *
  ****************************************/
 socket.on('get_active_games', function ()
 {
  console.log(user.nickname + ' requested his active games');
  socket.emit('set_active_games', toDTOs(games.getConnectedGamesOf(user.nickname)));
 });

 socket.on('leave_active_game', function (data)
 {
  if (!data || !data.gameID || isNaN(data.gameID))
  {
   return;
  }
  let game = games.leaveGame(data.gameID, user.nickname);
  if (game !== null)
  {
   if (game.createdBy == user.nickname)
   {
    console.log('Game: ' + game.gameID + ' deleted by ' + user.nickname + '!');
    //Delete chat for game
    activeGamesMessages.delete(game.gameID);
    //Remove lobby game from all clients
    socket.broadcast
          .emit('remove_lobby_game', game.gameID);
    //Remove active game from room clients
    io.to(game.gameID).emit('remove_active_game', game.gameID);
    //Destroy game room
    io.in(game.gameID).clients((err, clients) =>
                               {
                                if (clients.length > 0)
                                {
                                 clients.forEach(client_id =>
                                                 {
                                                  console.dir('' + users.get(client_id).nickname + ' left the room.');
                                                  io.sockets.sockets[client_id].leave(game.gameID);
                                                 });
                                }
                               });
    console.log('');
   }
   else
   {
    game = toDTO(game);
    console.log(user.nickname + ' left active game ' + game.gameID);
    //Remove user from game channel
    socket.leave(game.gameID);
    //Remove the client from the active game
    socket.emit('remove_active_game', game.gameID);
    //Add the game to the clients lobby
    socket.emit('add_lobby_game', game);
    //Update lobby game player numbers to all clients
    socket.broadcast.emit('update_lobby_game_players', game);
    //Update Active game player numbers to clients in room
    io.to(game.gameID).emit('update_active_game', game);
   }
  }
  else
  {
   //Error leaving
   console.log('Error! ' + user.nickname + ' cannot leave game ' + data.gameID);
  }
 });

 socket.on('init_active_game', function (data)
 {
  if (!data || typeof data.gameID === 'undefined' || isNaN(data.gameID))
  {
   return;
  }
  games.startGame(data.gameID, user.nickname);
 });

 /****************************************
  *                                      *
  *         STARTED GAMES                *
  *                                      *
  ****************************************/
 //GAMEID, ROW, COLUMN
 socket.on('game_click', function (data)
 {
  if(!data)
  {
   return;
  }
  if (typeof data.gameID === 'undefined' || isNaN(data.gameID))
  {
   return;
  }
  if (typeof data.row === 'undefined' || isNaN(data.row))
  {
   return;
  }
  if (typeof data.column === 'undefined' || isNaN(data.column))
  {
   return;
  }
  //IF CAN PLAY ON GAME
  let game = games.getStartedGameOf(data.gameID, user.nickname);
  if (game !== null)
  {
   game.play(data.row, data.column);
  }
  else
  {
   //Cant play
   console.log('Error! ' + user.nickname + ' cannot play on game ' + data.gameID);
  }
 });

 /****************************************
  *                                      *
  *          AUX                         *
  *                                      *
  ****************************************/
 let gameStartedCallBack = (game) =>
 {
  //Remove lobby game from all clients
  io.emit('remove_lobby_game', game.gameID);
  //Notify game has started
  io.to(game.gameID).emit('update_active_game', toDTO(game));
  io.to(game.gameID).emit('active_game_started', game.gameID);
  users.forEach((us, socketID) =>
                {
                 if (us.nickname == game.createdBy)
                 {
                  io.to(socketID).emit('your_turn', game.gameID);
                 }
                });
 };

 let turnPieceCallback = (game, pieceImage, data) =>
 {
  io.to(game.gameID).emit('show_piece', {
   gameID: game.gameID,
   row: data.row,
   column: data.column,
   piece: pieceImage
  });
 };

 let turnEndedCallBack = (g, notify) =>
 {
  //1SECOND AFTER TURN ENDED CALLBACK
  io.to(g.gameID).emit('update_active_game', toDTO(g));

  if (notify && g.players.length !== 1)
  {
   users.forEach((us, socketID) =>
                 {
                  if (us.nickname == g.playerToPlay().nickname)
                  {
                   io.to(socketID).emit('your_turn', g.gameID);
                  }
                 });
  }
 };

 let gameEndedCallBack = (g) =>
 {
  io.to(g.gameID).emit('update_active_game', toDTO(g));
  io.to(g.gameID).emit('active_game_ended', {
   gameID: g.gameID,
   winner: g.winner
  });
 };


});


let toDTOs = function (games)
{
 let gamesDTO = [];
 for (let game of games)
 {
  gamesDTO.push(toDTO(game));
 }
 return gamesDTO;
};

let toDTO = function (game)
{
 //DEEP COPY
 let clientGame = JSON.parse(JSON.stringify(game));
 if (game.gameStarted)
 {
  for (let row = 0; row < clientGame.rows; ++row)
  {
   clientGame.board[row] = [];
   for (let column = 0; column < clientGame.columns; ++column)
   {
    switch (game.board[row][column].state)
    {
     case game.PieceState.HIDDEN:
      clientGame.board[row][column] = game.hidden.path;
      break;
     case game.PieceState.EMPTY:
      clientGame.board[row][column] = 'empty.png';
      break;
     case game.PieceState.SHOWN:
      clientGame.board[row][column] = game.board[row][column].path;
      break;
    }
   }
  }
 }
 clientGame.token = null;
 return clientGame;
};