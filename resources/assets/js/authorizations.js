let user = window.App.user;

module.exports = {
    // These functions may be replaced by more generic owns() function.

    // updateReply(reply) {
    //     return reply.user_id === user.id;
    // },
    //
    // updateThread(thread) {
    //     return thread.user_id === user.id;
    // },

    owns(model, prop = 'user_id') {
        return model[prop] === user.id;
    },

    isAdmin() {
        return ['JohnDoe'].includes(user.name);
    }
};
