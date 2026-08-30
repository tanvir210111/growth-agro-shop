<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FacebookPage;
use App\Models\BotReply;
use App\Models\CommentAutomation;

class FacebookChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * এটি টেস্টিং এর জন্য sample data তৈরি করবে
     */
    public function run(): void
    {
        // Sample Facebook Page তৈরি করা (আপনার নিজের data দিয়ে replace করুন)
        $facebookPage = FacebookPage::create([
            'user_id' => 1, // প্রথম user এর ID (আপনার actual user ID দিন)
            'page_id' => 'SAMPLE_PAGE_ID', // আপনার actual Facebook Page ID দিন
            'page_name' => 'আমার ব্যবসা পেইজ',
            'page_access_token' => 'SAMPLE_TOKEN', // আপনার actual Page Access Token দিন
            'is_connected' => true,
        ]);

        // Sample Bot Replies তৈরি করা
        $botReplies = [
            [
                'keyword' => 'হাই',
                'reply_text' => 'হাই! আপনাকে স্বাগতম। আমি কিভাবে সাহায্য করতে পারি? 😊',
                'reply_type' => 'text',
            ],
            [
                'keyword' => 'হেল্প',
                'reply_text' => "আপনি নিচের জিনিসগুলো জানতে পারবেন:\n✅ প্রোডাক্ট - আমাদের প্রোডাক্ট দেখুন\n✅ প্রাইস - মূল্য তালিকা\n✅ অর্ডার - অর্ডার করার নিয়ম\n✅ ডেলিভারি - ডেলিভারি তথ্য",
                'reply_type' => 'text',
            ],
            [
                'keyword' => 'প্রাইস',
                'reply_text' => 'আমাদের প্রাইস লিস্ট দেখুন',
                'reply_type' => 'image',
                'attachment' => [
                    'image_url' => 'https://example.com/uploads/price-list.jpg'
                ],
            ],
            [
                'keyword' => 'প্রোডাক্ট',
                'reply_text' => 'আমাদের জনপ্রিয় প্রোডাক্ট দেখুন',
                'reply_type' => 'product_carousel',
                'attachment' => [
                    'products' => [
                        [
                            'title' => 'প্রিমিয়াম টি-শার্ট',
                            'subtitle' => '৳৫০০ - উচ্চ মানের কটন',
                            'image_url' => 'https://example.com/uploads/tshirt.jpg',
                            'buttons' => [
                                [
                                    'type' => 'web_url',
                                    'url' => 'https://example.com/product/tshirt',
                                    'title' => 'অর্ডার করুন'
                                ]
                            ]
                        ],
                        [
                            'title' => 'স্পোর্টস শুজ',
                            'subtitle' => '৳১২০০ - কমফোর্টেবল ডিজাইন',
                            'image_url' => 'https://example.com/uploads/shoes.jpg',
                            'buttons' => [
                                [
                                    'type' => 'web_url',
                                    'url' => 'https://example.com/product/shoes',
                                    'title' => 'অর্ডার করুন'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            [
                'keyword' => 'ডেলিভারি',
                'reply_text' => "🚚 ডেলিভারি তথ্য:\n\n📍 ঢাকার ভিতরে: ১-২ দিন\n📍 ঢাকার বাইরে: ৩-৫ দিন\n💰 ডেলিভারি চার্জ: ৬০-১২০ টাকা\n\n✅ ক্যাশ অন ডেলিভারি সুবিধা আছে",
                'reply_type' => 'text',
            ],
        ];

        foreach ($botReplies as $reply) {
            BotReply::create([
                'facebook_page_id' => $facebookPage->id,
                'keyword' => $reply['keyword'],
                'reply_text' => $reply['reply_text'],
                'reply_type' => $reply['reply_type'],
                'attachment' => $reply['attachment'] ?? null,
            ]);
        }

        // Sample Comment Automations তৈরি করা
        $commentAutomations = [
            [
                'trigger_word' => 'অর্ডার',
                'reply_comment' => 'ধন্যবাদ আগ্রহের জন্য! আমরা আপনাকে ইনবক্সে মেসেজ পাঠিয়েছি। ✅',
                'private_reply' => "হ্যালো! 👋\n\nঅর্ডার করতে এই লিংকে ক্লিক করুন:\n🔗 https://example.com/order\n\nঅথবা আমাদের কল করুন: 01700-000000",
            ],
            [
                'trigger_word' => 'প্রাইস',
                'reply_comment' => 'প্রাইস জানতে আপনার ইনবক্স চেক করুন। 📥',
                'private_reply' => "আমাদের প্রাইস লিস্ট দেখুন:\n🔗 https://example.com/pricing\n\nবিস্তারিত জানতে কল করুন: 01700-000000",
            ],
            [
                'trigger_word' => 'ইনবক্স',
                'reply_comment' => 'চেক করুন আপনার ইনবক্স! 📩',
                'private_reply' => "আমরা এখানে আছি সাহায্যের জন্য! 😊\n\nকিছু জানার থাকলে জিজ্ঞাসা করুন।",
            ],
        ];

        foreach ($commentAutomations as $automation) {
            CommentAutomation::create([
                'facebook_page_id' => $facebookPage->id,
                'trigger_word' => $automation['trigger_word'],
                'reply_comment' => $automation['reply_comment'],
                'private_reply' => $automation['private_reply'],
            ]);
        }

        echo "✅ Facebook Chatbot sample data created successfully!\n";
    }
}

