# Quick, Prompt!

By Google Creative Lab

## Contents

- [About](#about)
- [How it Works](#how-it-works)
- [Requirements](#requirements)
- [Developer Setup](#developer-setup)
- [Contributors](#contributors)
- [License](#license)
- [Notes](#notes)

## About

_Quick, Prompt!_ is a game where you try to get your AI teammate to guess a given word without using the word itself or any of the “forbidden” words.

![Quick Prompt demo gif](https://storage.googleapis.com/experiments-uploads/quick-prompt/quick-prompt.gif)

A live demo of _Quick, Prompt!_ is available at [quickprompt.withgoogle.com](https://quickprompt.withgoogle.com).

This demo is an example of how you can use the PaLM API to build applications that leverage Google’s state of the art large language models (LLMs).

The PaLM API consists of two APIs, each with a distinct method for generating content:

- The Chat API can be used to generate candidate `Message` responses to input messages via the `generateMessage()` function.
- The Text API can be used to generate candidate `TextCompletion` responses to input strings via the `generateText()` function.

This demo uses the Chat API. If you’re looking for a demo that uses the Text API, see [_List It_](https://github.com/google/generative-ai-docs/tree/main/demos/palm/web/list-it).

## How it Works

We can guide the model’s behavior in a back-and-forth conversation using a __prompt__, which provides examples of how the model should respond to user inputs.

Below is an excerpt of the prompt used in _Quick, Prompt!_:

```js
{
    // Context to steer model responses (optional)
    context: "We are going to play a game where you try and guess the word I'm thinking of.
    I'll start by giving you an initial hint, after which you should make your first guess.
    If you don't get it right, I'll provide an additional hint, and then you'll guess again.
    This process will continue until you guess correctly, or we run out of time.
    Make sure to enclose the word you're guessing within [square brackets].",
    
    // Examples to further tune model responses (optional)
    examples: [
        {
            input: {
                author: "user",
                content: "This is a thing into which feathers go, and it makes it more comfortable to sit."
            },
            output: {
                author: "model",
                content: "Hmm, ... is it a [pillow]?"
            }
        },
        {
            input: {
                author: "user",
                content: "You might find this on a couch."
            },
            output: {
                author: "model",
                content: "Ah, okay, I've got it! It's a [cushion]!"
            }
        },
        ...
    ],

    // The conversation, as an array of alternating user/model turns (required)
    messages: [...]
}
```

The Chat API is suitable for use cases that require the model to base its responses on previous interactions within a session. In the case of _Quick, Prompt!_, the model references previous hints along with the most recent hint for a target word as it tries to guess that word. We keep track of the alternating user/model turns in the `messages` array, which is included in the prompt object that we send to the model. With each call to `generateMessage()`, the model generates a response to the final string in `messages` based on the provided `context`, `examples`, and previous messages.

You can find the complete prompt in `src/lib/priming.js`.

## Requirements

- Node.js (version 18.15.0 or higher)
- Firebase project

Make sure you have either `npm` or `yarn` set up on your machine.

## Developer Setup

Although the PaLM API provides a [REST resource](https://developers.generativeai.google/api/rest/generativelanguage/models?hl=en), it is best practice to avoid embedding API keys directly into code (or in files inside your application’s source tree). If you want to call the PaLM API from the client side as we do in this demo, we recommend using a Firebase project with the Call PaLM API Securely extension enabled.

To set up Firebase:

1. Create a Firebase project at https://console.firebase.google.com.

2. Add a web app to your Firebase project and follow the on-screen instructions to add or install the Firebase SDK.

3. Go to https://console.cloud.google.com and select your Firebase project. Then go to _Security > Secret Manger_ using the left-side menu and make sure the Secret Manager API is enabled.

4. If you don’t already have an API key for the PaLM API, follow [these instructions](https://developers.generativeai.google/tutorials/setup) to get one.

5. Install the Call PaLM API Securely extension from the [Firebase Extensions Marketplace](https://extensions.dev/extensions). Follow the on-screen instructions to configure the extension.

    __NOTE__: Your project must be on the Blaze (pay as you go) plan to install the extension.

6. Enable anonymous authentication for your Firebase project by returning to https://console.firebase.google.com and selecting _Build_ in the left panel. Then go to _Authentication > Sign-in method_ and make sure _Anonymous_ is enabled.

7. Return to https://console.cloud.google.com and select your Firebase project. Click _More Products_ at the bottom of the left-side menu, then scroll down and click _Cloud Functions_. Select each function and then click _Permissions_ at the top. Add `allUsers` to the Cloud Functions Invoker role.

The above instructions assume that this demo will be used for individual/experimental purposes. If you anticipate broader usage, enable App Check in the Firebase extension during installation and see https://firebase.google.com/docs/app-check for an in-depth implementation guide.

To run the application locally:

1. Clone the repo to your local machine.

2. Run `npm i` or `yarn` in the root folder to install dependencies.

3. Add your Firebase info to `src/lib/firebase.config.js`.

4. Run `npm run dev` or `yarn dev` to start the application. The application will be served on localhost:5555. You can change the port in `vite.config.js` if desired.

## Contributors

- [Aaron Wade](https://github.com/aaron-wade)
- [Pixel Perfect Development](https://github.com/madebypxlp)

## License

[Apache License, Version 2.0](https://www.apache.org/licenses/LICENSE-2.0)

## Notes

This is not an official Google product, but rather a demo developed by the Google Creative Lab. This repository is meant to provide a snapshot of what is possible at this moment in time, and we do not intend for it to evolve.

We encourage open sourcing projects as a way of learning from each other. Please respect our and other creators’ rights—including copyright and trademark rights (when present)—when sharing these works or creating derivative work. If you want more info about Google's policies, you can find that [here](https://about.google/brand-resource-center/).